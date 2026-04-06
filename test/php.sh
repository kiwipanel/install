#!/usr/bin/env bash
# ==============================================================================
# KiwiPanel PHP Feature Test Script
# ==============================================================================
#
# Tests all PHP-related features via agent Unix socket + panel UI URLs.
# Run on the VPS as root (agent endpoints require local access).
#
# Usage:
#   chmod +x test/manual/test_php_features.sh
#   ./test/manual/test_php_features.sh              # Run all tests
#   ./test/manual/test_php_features.sh agent        # Agent-only tests
#   ./test/manual/test_php_features.sh ui           # Print UI test URLs
#   ./test/manual/test_php_features.sh security     # Security isolation tests only
#   ./test/manual/test_php_features.sh quick        # Quick smoke test
#
# Prerequisites:
#   - kiwipanel-agent running
#   - OpenLiteSpeed running
#   - At least one website created
# ==============================================================================

set -euo pipefail

# ── Config ─────────────────────────────────────────────────────────────────────
SOCKET="/run/kiwipanel/agent.sock"
PANEL_URL="${PANEL_URL:-https://localhost:8443}"
PASSCODE="${PASSCODE:-}"  # Set if agent requires auth header

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

PASS=0
FAIL=0
SKIP=0
ERRORS=()

# ── Helpers ────────────────────────────────────────────────────────────────────

print_header() {
    echo ""
    echo -e "${BOLD}════════════════════════════════════════════════════════════════${NC}"
    echo -e "${BOLD}  $1${NC}"
    echo -e "${BOLD}════════════════════════════════════════════════════════════════${NC}"
}

print_section() {
    echo ""
    echo -e "${CYAN}── $1 ──${NC}"
}

pass() {
    echo -e "  ${GREEN}✓${NC} $1"
    PASS=$((PASS + 1))
}

fail() {
    echo -e "  ${RED}✗${NC} $1"
    FAIL=$((FAIL + 1))
    ERRORS+=("$1")
}

skip() {
    echo -e "  ${YELLOW}○${NC} $1 (skipped)"
    SKIP=$((SKIP + 1))
}

info() {
    echo -e "  ${CYAN}ℹ${NC} $1"
}

# Agent HTTP call via Unix socket
# Usage: agent_get "/v1/php/versions"
agent_get() {
    local path="$1"
    curl -s --unix-socket "$SOCKET" "http://localhost${path}" \
        -H "Content-Type: application/json" \
        ${PASSCODE:+-H "X-Agent-Passcode: $PASSCODE"} \
        2>/dev/null
}

# Usage: agent_post "/v1/php/security-ini" '{"key":"value"}'
agent_post() {
    local path="$1"
    local body="${2:-{}}"
    curl -s --unix-socket "$SOCKET" "http://localhost${path}" \
        -X POST \
        -H "Content-Type: application/json" \
        ${PASSCODE:+-H "X-Agent-Passcode: $PASSCODE"} \
        -d "$body" \
        2>/dev/null
}

# Usage: agent_put "/v1/php/8.4/config" '{"mode":"directives",...}'
agent_put() {
    local path="$1"
    local body="${2:-{}}"
    curl -s --unix-socket "$SOCKET" "http://localhost${path}" \
        -X PUT \
        -H "Content-Type: application/json" \
        ${PASSCODE:+-H "X-Agent-Passcode: $PASSCODE"} \
        -d "$body" \
        2>/dev/null
}

# Check if JSON response contains a key with expected value
# Usage: json_check "$response" ".versions" "not_empty"
#        json_check "$response" ".success" "true"
json_check() {
    local json="$1"
    local jq_path="$2"
    local expected="$3"
    local actual

    actual=$(echo "$json" | jq -r "$jq_path" 2>/dev/null || echo "PARSE_ERROR")

    case "$expected" in
        not_empty)
            [[ -n "$actual" && "$actual" != "null" && "$actual" != "PARSE_ERROR" ]]
            ;;
        not_null)
            [[ "$actual" != "null" && "$actual" != "PARSE_ERROR" ]]
            ;;
        *)
            [[ "$actual" == "$expected" ]]
            ;;
    esac
}

# Get first vhost name from OLS config
get_first_vhost() {
    local vhosts_dir="/usr/local/lsws/vhosts"
    if [[ -d "$vhosts_dir" ]]; then
        ls "$vhosts_dir" | head -1
    fi
}

# ── Prerequisite Checks ───────────────────────────────────────────────────────

check_prerequisites() {
    print_header "Prerequisite Checks"

    # Check running as root
    if [[ $EUID -eq 0 ]]; then
        pass "Running as root"
    else
        fail "Must run as root (agent socket requires root)"
        exit 1
    fi

    # Check agent socket exists
    if [[ -S "$SOCKET" ]]; then
        pass "Agent socket exists: $SOCKET"
    else
        fail "Agent socket not found: $SOCKET"
        echo "    Is kiwipanel-agent running? Try: systemctl start kiwipanel-agent"
        exit 1
    fi

    # Check agent responds
    local resp
    resp=$(agent_get "/v1/health" 2>/dev/null || echo "")
    if [[ -n "$resp" ]]; then
        pass "Agent is responding"
    else
        fail "Agent not responding on socket"
        exit 1
    fi

    # Check OLS running
    if systemctl is-active --quiet lsws 2>/dev/null; then
        pass "OpenLiteSpeed is running"
    else
        skip "OpenLiteSpeed not running (some tests will fail)"
    fi

    # Check jq available
    if command -v jq &>/dev/null; then
        pass "jq is installed"
    else
        fail "jq is required: apt install jq"
        exit 1
    fi

    # Detect first vhost
    VHOST=$(get_first_vhost)
    if [[ -n "$VHOST" ]]; then
        pass "Found vhost: $VHOST"
    else
        skip "No vhosts found (per-website tests will be skipped)"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# SECTION 1: PHP VERSION DETECTION
# ══════════════════════════════════════════════════════════════════════════════

test_php_versions() {
    print_header "1. PHP Version Detection"

    print_section "GET /v1/php/versions"
    local resp
    resp=$(agent_get "/v1/php/versions")
    info "Response: $resp"

    if json_check "$resp" ".versions" "not_empty"; then
        pass "Versions array returned"
    else
        fail "No versions returned"
        return
    fi

    local count
    count=$(echo "$resp" | jq '.versions | length')
    if [[ "$count" -gt 0 ]]; then
        pass "Found $count PHP version(s): $(echo "$resp" | jq -r '.versions | join(", ")')"
    else
        fail "Empty versions array"
    fi

    # Store first version for later tests
    PHP_VERSION=$(echo "$resp" | jq -r '.versions[0]')
    info "Using PHP $PHP_VERSION for subsequent tests"

    local default_ver
    default_ver=$(echo "$resp" | jq -r '.default_version')
    if [[ -n "$default_ver" && "$default_ver" != "null" ]]; then
        pass "Default version: $default_ver"
    else
        skip "No default_version field"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# SECTION 2: PHP WORKER STATUS
# ══════════════════════════════════════════════════════════════════════════════

test_php_workers() {
    print_header "2. PHP Worker Management"

    print_section "GET /v1/php/$PHP_VERSION/status"
    local resp
    resp=$(agent_get "/v1/php/$PHP_VERSION/status")
    info "Response (truncated): $(echo "$resp" | jq -c '{version,installed,worker_count,total_memory_mb}')"

    if json_check "$resp" ".installed" "true"; then
        pass "PHP $PHP_VERSION is installed"
    else
        fail "PHP $PHP_VERSION not detected as installed"
    fi

    if json_check "$resp" ".cli_version" "not_empty"; then
        pass "CLI version: $(echo "$resp" | jq -r '.cli_version' | head -1)"
    else
        skip "No CLI version string"
    fi

    local wcount
    wcount=$(echo "$resp" | jq -r '.worker_count // 0')
    if [[ "$wcount" -gt 0 ]]; then
        pass "$wcount worker(s) running, total memory: $(echo "$resp" | jq -r '.total_memory_mb')MB"
    else
        skip "No workers running (visit a PHP page to spawn workers)"
    fi

    # Test graceful restart
    print_section "POST /v1/php/$PHP_VERSION/restart-workers (SIGUSR1)"
    resp=$(agent_post "/v1/php/$PHP_VERSION/restart-workers")
    info "Response: $resp"

    if json_check "$resp" ".success" "true"; then
        pass "Graceful restart succeeded ($(echo "$resp" | jq -r '.killed') workers signaled)"
    else
        skip "Graceful restart: $(echo "$resp" | jq -r '.message // .error')"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# SECTION 3: GLOBAL SECURITY INI
# ══════════════════════════════════════════════════════════════════════════════

test_security_ini() {
    print_header "3. Global Security INI (PHP_INI_SYSTEM)"

    print_section "GET /v1/php/security-ini?version=$PHP_VERSION"
    local resp
    resp=$(agent_get "/v1/php/security-ini?version=$PHP_VERSION")
    info "Response: $(echo "$resp" | jq -c '.')"

    if json_check "$resp" ".version" "$PHP_VERSION"; then
        pass "Correct version returned"
    else
        fail "Wrong version in response"
    fi

    local exists
    exists=$(echo "$resp" | jq -r '.exists')
    if [[ "$exists" == "true" ]]; then
        pass "Security ini exists"

        local df
        df=$(echo "$resp" | jq -r '.disable_functions')
        if [[ -n "$df" && "$df" != "null" ]]; then
            local func_count
            func_count=$(echo "$df" | tr ',' '\n' | wc -l | tr -d ' ')
            pass "disable_functions: $func_count functions blocked"
        else
            fail "disable_functions is empty"
        fi

        local ep
        ep=$(echo "$resp" | jq -r '.expose_php')
        if [[ "$ep" == "Off" ]]; then
            pass "expose_php: Off"
        else
            fail "expose_php should be Off, got: $ep"
        fi
    else
        info "Security ini doesn't exist yet — testing write..."
    fi

    # Test writing security ini
    print_section "POST /v1/php/security-ini (write)"
    local write_body
    write_body=$(cat <<'ENDJSON'
{
    "php_versions": [],
    "disable_functions": "exec,system,passthru,shell_exec,proc_open,popen,proc_close,proc_get_status,proc_nice,proc_terminate,pcntl_exec,pcntl_fork,pcntl_signal,pcntl_waitpid,pcntl_wexitstatus,pcntl_wifexited,pcntl_wifsignaled,pcntl_wifstopped,pcntl_wstopsig,pcntl_wtermsig,pcntl_alarm,dl,putenv,symlink,link,chown,chgrp,chmod",
    "expose_php": "Off",
    "allow_url_include": "Off"
}
ENDJSON
)

    resp=$(agent_post "/v1/php/security-ini" "$write_body")
    info "Response: $(echo "$resp" | jq -c '.')"

    if json_check "$resp" ".success" "true"; then
        pass "Security ini written: $(echo "$resp" | jq -r '.message')"
    else
        fail "Failed to write security ini: $(echo "$resp" | jq -r '.error // .message')"
    fi

    # Verify file permissions
    print_section "Verify ini file permissions"
    local short_ver="${PHP_VERSION//./}"
    local ini_path="/usr/local/lsws/lsphp${short_ver}/etc/php/${PHP_VERSION}/mods-available/20-kiwipanel-security.ini"

    if [[ -f "$ini_path" ]]; then
        local perms
        perms=$(stat -c '%a' "$ini_path" 2>/dev/null || stat -f '%Lp' "$ini_path" 2>/dev/null)
        if [[ "$perms" == "644" ]]; then
            pass "File permissions correct: $perms ($ini_path)"
        else
            fail "File permissions wrong: $perms (expected 644) — $ini_path"
        fi
    else
        skip "Ini file not found at $ini_path"
    fi

    # Verify lsphp actually loads it
    print_section "Verify lsphp loads the ini"
    local lsphp_bin="/usr/local/lsws/lsphp${short_ver}/bin/lsphp"
    if [[ -x "$lsphp_bin" ]]; then
        local parsed
        parsed=$("$lsphp_bin" -i 2>/dev/null | grep -c "20-kiwipanel-security.ini" || true)
        if [[ "$parsed" -gt 0 ]]; then
            pass "lsphp$short_ver loads 20-kiwipanel-security.ini"
        else
            fail "lsphp$short_ver does NOT load security ini — check permissions / scan dir"
            info "Debug: $lsphp_bin -i | grep -i 'scan'"
            "$lsphp_bin" -i 2>/dev/null | grep -i "scan\|additional" | head -3
        fi
    else
        skip "lsphp binary not found at $lsphp_bin"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# SECTION 4: PHP EXTENSIONS
# ══════════════════════════════════════════════════════════════════════════════

test_php_extensions() {
    print_header "4. PHP Extensions"

    print_section "GET /v1/php/$PHP_VERSION/extensions"
    local resp
    resp=$(agent_get "/v1/php/$PHP_VERSION/extensions")

    local total
    total=$(echo "$resp" | jq '.extensions | length')
    info "Total extensions: $total"

    if [[ "$total" -gt 0 ]]; then
        pass "Extensions list returned ($total items)"
    else
        fail "No extensions returned"
        return
    fi

    # Count by status
    local installed available builtin
    installed=$(echo "$resp" | jq '[.extensions[] | select(.status=="installed")] | length')
    available=$(echo "$resp" | jq '[.extensions[] | select(.status=="available")] | length')
    builtin=$(echo "$resp" | jq '[.extensions[] | select(.status=="built-in")] | length')
    pass "Installed: $installed, Available: $available, Built-in: $builtin"

    # List installed ones
    info "Installed extensions: $(echo "$resp" | jq -r '[.extensions[] | select(.status=="installed") | .name] | join(", ")')"

    # Check for custom extensions
    local custom_count
    custom_count=$(echo "$resp" | jq '[.extensions[] | select(.custom==true)] | length')
    if [[ "$custom_count" -gt 0 ]]; then
        info "Custom (non-curated) extensions: $(echo "$resp" | jq -r '[.extensions[] | select(.custom==true) | .name] | join(", ")')"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# SECTION 5: PER-WEBSITE PHP SETTINGS
# ══════════════════════════════════════════════════════════════════════════════

test_website_php() {
    print_header "5. Per-Website PHP Settings"

    if [[ -z "${VHOST:-}" ]]; then
        skip "No vhost found — skipping per-website tests"
        return
    fi

    print_section "GET /v1/vhosts/$VHOST/php"
    local resp
    resp=$(agent_get "/v1/vhosts/$VHOST/php")
    info "Response (truncated): $(echo "$resp" | jq -c '{php_version, settings: (.settings | keys | length | tostring + " settings"), security: (.security // "none")}')"

    if json_check "$resp" ".php_version" "not_empty"; then
        pass "PHP version: $(echo "$resp" | jq -r '.php_version')"
    else
        fail "No PHP version in response"
    fi

    local setting_count
    setting_count=$(echo "$resp" | jq '.settings | length')
    if [[ "$setting_count" -gt 0 ]]; then
        pass "$setting_count settings parsed from vhconf"
    else
        skip "No settings in vhconf"
    fi

    # Check security directives
    local sec
    sec=$(echo "$resp" | jq -r '.security // empty')
    if [[ -n "$sec" ]]; then
        local ob
        ob=$(echo "$resp" | jq -r '.security.open_basedir // "not set"')
        pass "Security directives present (open_basedir: $ob)"
    else
        info "No security directives in vhconf (may use global defaults)"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# SECTION 6: GLOBAL PHP.INI
# ══════════════════════════════════════════════════════════════════════════════

test_php_config() {
    print_header "6. Global php.ini"

    print_section "GET /v1/php/$PHP_VERSION/config"
    local resp
    resp=$(agent_get "/v1/php/$PHP_VERSION/config")

    if json_check "$resp" ".ini_path" "not_empty"; then
        pass "INI path: $(echo "$resp" | jq -r '.ini_path')"
    else
        fail "No ini_path in response"
        return
    fi

    local dir_count
    dir_count=$(echo "$resp" | jq '.directives | length')
    if [[ "$dir_count" -gt 0 ]]; then
        pass "$dir_count directives parsed"
        info "memory_limit: $(echo "$resp" | jq -r '.directives.memory_limit // "not set"')"
        info "max_execution_time: $(echo "$resp" | jq -r '.directives.max_execution_time // "not set"')"
        info "upload_max_filesize: $(echo "$resp" | jq -r '.directives.upload_max_filesize // "not set"')"
    else
        skip "No directives parsed (php.ini might be minimal)"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# SECTION 7: LSAPI TUNING
# ══════════════════════════════════════════════════════════════════════════════

test_lsapi() {
    print_header "7. LSAPI Tuning"

    print_section "GET /v1/php/$PHP_VERSION/lsapi"
    local resp
    resp=$(agent_get "/v1/php/$PHP_VERSION/lsapi")

    if json_check "$resp" ".defaults" "not_null"; then
        pass "LSAPI defaults returned"
        info "max_conns: $(echo "$resp" | jq -r '.defaults.max_conns // "?"')"
        info "php_lsapi_children: $(echo "$resp" | jq -r '.defaults.php_lsapi_children // "?"')"
        info "lsapi_avoid_fork: $(echo "$resp" | jq -r '.defaults.lsapi_avoid_fork // "?"')"
    else
        skip "No LSAPI defaults (may not have been configured yet)"
    fi

    local wcount
    wcount=$(echo "$resp" | jq -r '.website_count // 0')
    info "$wcount website(s) using PHP $PHP_VERSION"
}

# ══════════════════════════════════════════════════════════════════════════════
# SECTION 8: DIAGNOSTIC ENDPOINTS
# ══════════════════════════════════════════════════════════════════════════════

test_diagnostics() {
    print_header "8. Diagnostic Endpoints"

    print_section "GET /v1/php/$PHP_VERSION/phpinfo"
    local resp
    resp=$(agent_get "/v1/php/$PHP_VERSION/phpinfo")

    if json_check "$resp" ".html" "not_empty"; then
        local html_size
        html_size=$(echo "$resp" | jq -r '.html' | wc -c | tr -d ' ')
        pass "phpinfo() returned ($html_size bytes of HTML)"
    else
        fail "No phpinfo HTML returned"
    fi

    print_section "GET /v1/php/$PHP_VERSION/logs?lines=50"
    resp=$(agent_get "/v1/php/$PHP_VERSION/logs?lines=50")

    local not_found
    not_found=$(echo "$resp" | jq -r '.not_found // false')
    if [[ "$not_found" == "true" ]]; then
        skip "No PHP error log found"
    else
        local log_file
        log_file=$(echo "$resp" | jq -r '.log_file // "?"')
        pass "Error log: $log_file"
        local content_size
        content_size=$(echo "$resp" | jq -r '.content // ""' | wc -c | tr -d ' ')
        info "Log content: $content_size bytes"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# SECTION 9: SECURITY ISOLATION VERIFICATION
# ══════════════════════════════════════════════════════════════════════════════

test_security_isolation() {
    print_header "9. Security Isolation (Live PHP Tests)"

    if [[ -z "${VHOST:-}" ]]; then
        skip "No vhost — skipping isolation tests"
        return
    fi

    # Find the website's document root
    local vhconf="/usr/local/lsws/vhosts/$VHOST/vhconf.conf"
    if [[ ! -f "$vhconf" ]]; then
        skip "vhconf.conf not found for $VHOST"
        return
    fi

    # Extract docRoot from vhconf
    local docroot
    docroot=$(grep -oP 'docRoot\s+\K\S+' "$vhconf" | head -1)

    # Try to resolve $VH_ROOT
    if [[ "$docroot" == *'$VH_ROOT'* ]]; then
        local vhroot
        vhroot=$(grep -oP 'vhRoot\s+\K\S+' "$vhconf" | head -1)
        docroot="${docroot/\$VH_ROOT/$vhroot}"
    fi

    if [[ ! -d "$docroot" ]]; then
        # Fallback: try common pattern
        local home_dir
        home_dir=$(grep -oP 'vhRoot\s+\K\S+' "$vhconf" | head -1)
        docroot="${home_dir}/public_html"
    fi

    if [[ ! -d "$docroot" ]]; then
        skip "Cannot determine docroot for $VHOST"
        return
    fi

    info "Document root: $docroot"

    # Create test file
    local test_file="$docroot/_kiwi_security_test.php"
    cat > "$test_file" << 'PHPEOF'
<?php
header('Content-Type: application/json');
$results = [];

// Test 1: disable_functions
$dangerous = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen'];
foreach ($dangerous as $func) {
    $results['disable_functions'][$func] = function_exists($func) ? 'ENABLED (BAD!)' : 'DISABLED (GOOD)';
}

// Test 2: open_basedir
$results['open_basedir'] = ini_get('open_basedir') ?: 'NOT SET';

// Test 3: Can read /etc/passwd?
$passwd = @file_get_contents('/etc/passwd');
$results['read_etc_passwd'] = $passwd ? 'EXPOSED (BAD!)' : 'BLOCKED (GOOD)';

// Test 4: Can read /etc/shadow?
$shadow = @file_get_contents('/etc/shadow');
$results['read_etc_shadow'] = $shadow ? 'EXPOSED (BAD!)' : 'BLOCKED (GOOD)';

// Test 5: Can read other users' dirs?
$home = @scandir('/home');
$results['list_home'] = $home ? 'VISIBLE: ' . implode(',', array_slice($home, 0, 5)) : 'BLOCKED (GOOD)';

// Test 6: expose_php
$results['expose_php'] = ini_get('expose_php') ? 'On (BAD!)' : 'Off (GOOD)';

// Test 7: allow_url_include
$results['allow_url_include'] = ini_get('allow_url_include') ? 'On (BAD!)' : 'Off (GOOD)';

// Test 8: Session save path
$results['session_save_path'] = ini_get('session.save_path') ?: '/tmp (shared — potential issue)';

// Test 9: PHP version
$results['php_version'] = phpversion();

// Test 10: SAPI
$results['sapi'] = php_sapi_name();

// Test 11: Current user
$results['current_user'] = get_current_user();

// Test 12: disable_functions full list
$results['disable_functions_list'] = ini_get('disable_functions') ?: 'NONE SET';

echo json_encode($results, JSON_PRETTY_PRINT);
PHPEOF

    # Set ownership to website user
    local site_user
    site_user=$(grep -oP 'extUser\s+\K\S+' "$vhconf" | head -1)
    if [[ -n "$site_user" ]]; then
        chown "$site_user":"$site_user" "$test_file" 2>/dev/null || true
    fi

    pass "Test file created: $test_file"

    # Try to fetch via HTTP
    # Extract domain from vhost name (replace _ with .)
    local domain="${VHOST//_/.}"
    local test_url="https://${domain}/_kiwi_security_test.php"

    print_section "Fetching $test_url"
    info "If this fails, try accessing manually in browser:"
    info "  $test_url"

    local http_resp
    http_resp=$(curl -sk "$test_url" 2>/dev/null || echo "CURL_FAILED")

    if [[ "$http_resp" == "CURL_FAILED" || -z "$http_resp" ]]; then
        skip "Cannot reach $test_url via HTTP — test file is ready for manual browser test"
        info "Test manually: curl -sk $test_url | jq ."
    elif echo "$http_resp" | jq . &>/dev/null; then
        # Parse JSON results
        echo ""
        info "Security test results:"
        echo "$http_resp" | jq -C '.'
        echo ""

        # Validate each result
        local df_exec df_shell
        df_exec=$(echo "$http_resp" | jq -r '.disable_functions.exec // "?"')
        df_shell=$(echo "$http_resp" | jq -r '.disable_functions.shell_exec // "?"')

        if [[ "$df_exec" == *"DISABLED"* ]]; then
            pass "exec() is disabled"
        else
            fail "exec() is NOT disabled: $df_exec"
        fi

        if [[ "$df_shell" == *"DISABLED"* ]]; then
            pass "shell_exec() is disabled"
        else
            fail "shell_exec() is NOT disabled: $df_shell"
        fi

        local ob
        ob=$(echo "$http_resp" | jq -r '.open_basedir')
        if [[ -n "$ob" && "$ob" != "NOT SET" ]]; then
            pass "open_basedir is set: $ob"
        else
            fail "open_basedir is NOT set"
        fi

        local etc_passwd
        etc_passwd=$(echo "$http_resp" | jq -r '.read_etc_passwd')
        if [[ "$etc_passwd" == *"BLOCKED"* ]]; then
            pass "/etc/passwd is blocked"
        else
            fail "/etc/passwd is readable: $etc_passwd"
        fi

        local expose
        expose=$(echo "$http_resp" | jq -r '.expose_php')
        if [[ "$expose" == *"Off"* ]]; then
            pass "expose_php is Off"
        else
            fail "expose_php is On"
        fi

        local aui
        aui=$(echo "$http_resp" | jq -r '.allow_url_include')
        if [[ "$aui" == *"Off"* ]]; then
            pass "allow_url_include is Off"
        else
            fail "allow_url_include is On"
        fi
    else
        # Non-JSON response (might be 500 error)
        fail "Got non-JSON response (likely 500 error due to disable_functions)"
        info "Response: $(echo "$http_resp" | head -5)"
        info "This means disable_functions is blocking — the test PHP itself uses no blocked functions"
        info "If you see 500, check: tail -20 /usr/local/lsws/logs/stderr.log"
    fi

    # Cleanup
    rm -f "$test_file"
    info "Test file cleaned up"
}

# ══════════════════════════════════════════════════════════════════════════════
# SECTION 10: UI TEST URLs
# ══════════════════════════════════════════════════════════════════════════════

print_ui_urls() {
    print_header "10. UI Test URLs (Manual Browser Testing)"

    echo ""
    echo -e "${BOLD}Global PHP Settings (Admin → Settings):${NC}"
    echo "  PHP Security:      ${PANEL_URL}/settings/php-security"
    echo "  PHP Options:       ${PANEL_URL}/settings/php-options"
    echo ""
    echo -e "${BOLD}PHP Management (Admin → Settings → Services):${NC}"
    echo "  Services page:     ${PANEL_URL}/settings/services"

    # List per-version pages
    local resp
    resp=$(agent_get "/v1/php/versions" 2>/dev/null)
    if [[ -n "$resp" ]]; then
        local versions
        versions=$(echo "$resp" | jq -r '.versions[]' 2>/dev/null)
        while IFS= read -r ver; do
            echo "  PHP $ver Manage:    ${PANEL_URL}/settings/services/php/$ver"
        done <<< "$versions"
    fi

    echo ""
    echo -e "${BOLD}Per-Website PHP (Website → PHP tab):${NC}"

    if [[ -d "/usr/local/lsws/vhosts" ]]; then
        # We need website IDs from the database, so just show the pattern
        echo "  Pattern:           ${PANEL_URL}/websites/{id}/php"
        echo "  (Get website ID from the websites list page)"
    fi

    echo ""
    echo -e "${BOLD}Things to Test in Browser:${NC}"
    echo "  1. PHP Security page → verify disable_functions list"
    echo "  2. PHP Security page → toggle expose_php On/Off → save → verify"
    echo "  3. PHP Options page → check default settings"
    echo "  4. Services page → see all PHP versions detected"
    echo "  5. PHP Manage page → Extensions tab → install/uninstall redis"
    echo "  6. PHP Manage page → Workers tab → restart workers"
    echo "  7. PHP Manage page → php.ini tab → edit memory_limit"
    echo "  8. PHP Manage page → LSAPI tab → adjust max_conns"
    echo "  9. PHP Manage page → phpinfo tab → verify loaded modules"
    echo "  10. PHP Manage page → Logs tab → check for errors"
    echo "  11. Website PHP page → change PHP version (e.g., 8.4 → 8.3)"
    echo "  12. Website PHP page → adjust memory_limit, upload_max_filesize"
    echo "  13. Website PHP page → verify open_basedir is set"
    echo "  14. Website PHP page → reset to defaults"
    echo ""
}

# ══════════════════════════════════════════════════════════════════════════════
# QUICK SMOKE TEST
# ══════════════════════════════════════════════════════════════════════════════

quick_smoke() {
    print_header "Quick Smoke Test"

    info "Testing core agent endpoints..."

    # 1. Versions
    local resp
    resp=$(agent_get "/v1/php/versions")
    if json_check "$resp" ".versions" "not_empty"; then
        PHP_VERSION=$(echo "$resp" | jq -r '.versions[0]')
        pass "PHP versions: $(echo "$resp" | jq -r '.versions | join(", ")')"
    else
        fail "PHP version detection failed"
        return
    fi

    # 2. Status
    resp=$(agent_get "/v1/php/$PHP_VERSION/status")
    if json_check "$resp" ".installed" "true"; then
        pass "PHP $PHP_VERSION status OK ($(echo "$resp" | jq -r '.worker_count') workers)"
    else
        fail "PHP $PHP_VERSION status failed"
    fi

    # 3. Security ini
    resp=$(agent_get "/v1/php/security-ini?version=$PHP_VERSION")
    local exists
    exists=$(echo "$resp" | jq -r '.exists')
    pass "Security ini exists=$exists for PHP $PHP_VERSION"

    # 4. Extensions
    resp=$(agent_get "/v1/php/$PHP_VERSION/extensions")
    local ext_count
    ext_count=$(echo "$resp" | jq '.extensions | length' 2>/dev/null || echo 0)
    pass "Extensions: $ext_count found"

    # 5. Per-website
    VHOST=$(get_first_vhost)
    if [[ -n "$VHOST" ]]; then
        resp=$(agent_get "/v1/vhosts/$VHOST/php")
        if json_check "$resp" ".php_version" "not_empty"; then
            pass "Website $VHOST PHP: $(echo "$resp" | jq -r '.php_version')"
        else
            fail "Website PHP settings failed for $VHOST"
        fi
    else
        skip "No vhosts for per-website test"
    fi

    # 6. phpinfo
    resp=$(agent_get "/v1/php/$PHP_VERSION/phpinfo")
    if json_check "$resp" ".html" "not_empty"; then
        pass "phpinfo() works"
    else
        fail "phpinfo() failed"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# SUMMARY
# ══════════════════════════════════════════════════════════════════════════════

print_summary() {
    echo ""
    echo -e "${BOLD}════════════════════════════════════════════════════════════════${NC}"
    echo -e "${BOLD}  RESULTS${NC}"
    echo -e "${BOLD}════════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "  ${GREEN}Passed:${NC}  $PASS"
    echo -e "  ${RED}Failed:${NC}  $FAIL"
    echo -e "  ${YELLOW}Skipped:${NC} $SKIP"
    echo ""

    if [[ $FAIL -gt 0 ]]; then
        echo -e "${RED}Failures:${NC}"
        for err in "${ERRORS[@]}"; do
            echo -e "  ${RED}✗${NC} $err"
        done
        echo ""
    fi

    if [[ $FAIL -eq 0 ]]; then
        echo -e "${GREEN}All tests passed! ✓${NC}"
    else
        echo -e "${RED}$FAIL test(s) failed${NC}"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# MAIN
# ══════════════════════════════════════════════════════════════════════════════

main() {
    local mode="${1:-all}"

    echo -e "${BOLD}KiwiPanel PHP Feature Test Suite${NC}"
    echo "$(date)"
    echo ""

    check_prerequisites

    case "$mode" in
        all)
            test_php_versions
            test_php_workers
            test_security_ini
            test_php_extensions
            test_website_php
            test_php_config
            test_lsapi
            test_diagnostics
            test_security_isolation
            print_ui_urls
            ;;
        agent)
            test_php_versions
            test_php_workers
            test_security_ini
            test_php_extensions
            test_website_php
            test_php_config
            test_lsapi
            test_diagnostics
            ;;
        security)
            test_php_versions
            test_security_ini
            test_security_isolation
            ;;
        ui)
            test_php_versions
            print_ui_urls
            ;;
        quick)
            quick_smoke
            ;;
        *)
            echo "Usage: $0 [all|agent|security|ui|quick]"
            exit 1
            ;;
    esac

    print_summary
}

main "$@"
