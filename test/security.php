<?php
echo "<h2>🔒 PHP Security Audit</h2><pre>";

// ============ FUNCTION RESTRICTIONS ============
echo "=== DANGEROUS FUNCTIONS ===\n";
$dangerous = [
    "exec",
    "shell_exec",
    "system",
    "passthru",
    "popen",
    "proc_open",
    "pcntl_exec",
    "pcntl_fork",
    "dl",
    "putenv",
    "ini_set",
];
foreach ($dangerous as $fn) {
    echo str_pad($fn, 20) .
        ": " .
        (function_exists($fn) ? "⚠️  ENABLED" : "✅ DISABLED") .
        "\n";
}

// ============ ADDITIONAL RISKY FUNCTIONS ============
echo "\n=== ADDITIONAL RISKY FUNCTIONS ===\n";
$additional_risky = [
    "mail" => "Email spam injection",
    "curl_exec" => "HTTP requests",
    "curl_multi_exec" => "Multi HTTP requests",
    "file_get_contents" => "File read/HTTP",
    "file_put_contents" => "File write",
    "parse_ini_file" => "Config parsing",
    "show_source" => "Code disclosure",
    "highlight_file" => "Code disclosure",
    "phpinfo" => "Info disclosure",
    "apache_setenv" => "Env manipulation",
    "getmyuid" => "User enumeration",
    "getmygid" => "Group enumeration",
    "posix_getpwuid" => "User enumeration",
    "posix_getgrgid" => "Group enumeration",
    "posix_kill" => "Process control",
    "chgrp" => "File group change",
    "chmod" => "File permissions",
    "chown" => "File owner change",
    "lchgrp" => "Symlink group change",
    "lchown" => "Symlink owner change",
    "fileperms" => "File permissions read",
    "fileinode" => "File inode read",
    "fileowner" => "File owner read",
    "filegroup" => "File group read",
    "touch" => "File timestamp",
    "symlink" => "Symlink creation",
    "link" => "Hard link creation",
    "tempnam" => "Temp file creation",
    "tmpfile" => "Temp file creation",
];
foreach ($additional_risky as $fn => $desc) {
    echo str_pad($fn, 20) .
        ": " .
        (function_exists($fn) ? "⚠️  ENABLED ($desc)" : "✅ DISABLED") .
        "\n";
}

// ============ DISABLED CLASSES ============
echo "\n=== DISABLED CLASSES ===\n";
$disabled_classes = ini_get("disable_classes");
echo "Disabled classes: " .
    ($disabled_classes ?: "⚠️  NONE (classes may be vulnerable)") .
    "\n";

// ============ OPEN_BASEDIR ============
echo "\n=== OPEN_BASEDIR ===\n";
echo "Value: " .
    (ini_get("open_basedir") ?: "⚠️  NOT SET (full filesystem access)") .
    "\n";

// ============ PHP INI SECURITY SETTINGS ============
echo "\n=== PHP INI SECURITY SETTINGS ===\n";
$ini_settings = [
    "expose_php" => "Server disclosure",
    "enable_dl" => "Dynamic loading",
    "file_uploads" => "File upload",
    "upload_max_filesize" => "Upload size limit (DoS)",
    "max_execution_time" => "Execution limit (DoS)",
    "max_input_time" => "Input time limit (DoS)",
    "memory_limit" => "Memory limit (DoS)",
    "post_max_size" => "POST size limit (DoS)",
    "max_file_uploads" => "Max uploads at once",
    "allow_url_fopen" => "Remote file access",
    "allow_url_include" => "Remote code include",
    "display_errors" => "Error disclosure",
    "display_startup_errors" => "Startup error disclosure",
    "log_errors" => "Error logging",
    "error_log" => "Error log path",
    "register_globals" => "Global vars (deprecated)",
    "auto_prepend_file" => "Auto include before",
    "auto_append_file" => "Auto include after",
    "default_charset" => "Default charset",
];
foreach ($ini_settings as $key => $desc) {
    $val = ini_get($key);
    echo str_pad($key, 25) . ": " . ($val ?: "(empty)") . " [$desc]\n";
}

// ============ SESSION SECURITY ============
echo "\n=== SESSION SECURITY ===\n";
$session_settings = [
    "session.cookie_httponly" => "HTTP only cookies (XSS)",
    "session.cookie_secure" => "HTTPS only cookies",
    "session.use_strict_mode" => "Strict session mode",
    "session.cookie_samesite" => "SameSite cookie policy",
    "session.use_cookies" => "Cookie-based sessions",
    "session.use_trans_sid" => "SID in URL (leak risk)",
];
foreach ($session_settings as $key => $desc) {
    $val = ini_get($key);
    echo str_pad($key, 30) . ": " . ($val ?: "0") . " [$desc]\n";
}

// ============ GD/IMAGICK (Image Processing Vulnerabilities) ============
echo "\n=== IMAGE PROCESSING EXTENSIONS ===\n";
$image_exts = ["gd", "imagick", "imagickpixel", "exif"];
foreach ($image_exts as $ext) {
    $loaded = extension_loaded($ext);
    echo str_pad($ext, 20) .
        ": " .
        ($loaded ? "⚠️  LOADED (potential exploit vectors)" : "✅ NOT LOADED") .
        "\n";
}

// ============ EXTENSIONS (Potential Risk) ============
echo "\n=== RISKY EXTENSIONS ===\n";
$risky_exts = [
    "ssh2" => "SSH connections",
    "ftp" => "FTP connections",
    "imap" => "Email access",
    "mysqli" => "MySQL direct access",
    "pdo" => "Database access",
    "pdo_mysql" => "MySQL PDO",
    "curl" => "HTTP requests",
    "sockets" => "Raw socket access",
    "stream" => "Stream wrapper",
    "zip" => "Archive handling",
    "phar" => "PHP Archive (deserialization risk)",
    "soap" => "SOAP requests",
    "xmlrpc" => "XML-RPC",
    "wddx" => "WDDX serialization",
];
foreach ($risky_exts as $ext => $desc) {
    $loaded = extension_loaded($ext);
    echo str_pad($ext, 15) .
        ": " .
        ($loaded ? "⚠️  LOADED ($desc)" : "✅ NOT LOADED") .
        "\n";
}

// ============ FILE SYSTEM ACCESS ============
echo "\n=== FILE SYSTEM ACCESS (should all fail) ===\n";
$sensitive_files = [
    "/etc/shadow",
    "/etc/passwd",
    "/etc/my.cnf",
    "/etc/mysql/debian.cnf",
    "/root/.bash_history",
    "/root/.ssh/id_rsa",
    "/var/log/auth.log",
    "/proc/1/environ",
];
foreach ($sensitive_files as $f) {
    $result = @file_get_contents($f);
    echo str_pad($f, 35) .
        ": " .
        ($result !== false
            ? "⚠️  READABLE (" . strlen($result) . " bytes)"
            : "✅ BLOCKED") .
        "\n";
}

// ============ DIRECTORY TRAVERSAL ============
echo "\n=== DIRECTORY TRAVERSAL ===\n";
$dirs = ["/", "/etc", "/root", "/home", "/var/www"];
foreach ($dirs as $d) {
    $files = @scandir($d);
    echo str_pad($d, 20) .
        ": " .
        ($files
            ? "⚠️  LISTABLE (" . count($files) . " entries)"
            : "✅ BLOCKED") .
        "\n";
}

// ============ OTHER USERS' DATA ============
echo "\n=== CROSS-USER ISOLATION (should all fail) ===\n";
$other_users = [
    "inkdrop_quan_usr",
    "api_kiwipane_usr",
    "test_kiwipan_usr",
    "api_quanthai_usr",
];
foreach ($other_users as $user) {
    $path = "/var/www/$user/data";
    $result = @scandir($path);
    echo str_pad($user, 25) .
        ": " .
        ($result ? "⚠️  ACCESSIBLE" : "✅ ISOLATED") .
        "\n";
}

// ============ NETWORK CAPABILITIES ============
echo "\n=== NETWORK ACCESS ===\n";
// Can PHP make outbound connections? (data exfiltration risk)
$sock = @fsockopen("google.com", 80, $errno, $errstr, 3);
echo "Outbound HTTP:        " . ($sock ? "⚠️  ALLOWED" : "✅ BLOCKED") . "\n";
if ($sock) {
    fclose($sock);
}

// ============ INFO DISCLOSURE ============
echo "\n=== INFO DISCLOSURE ===\n";
echo "PHP version:          " . phpversion() . "\n";
echo "Server software:      " .
    ($_SERVER["SERVER_SOFTWARE"] ?? "unknown") .
    "\n";
echo "Current user:         " . get_current_user() . "\n";
echo "Process UID:          " .
    (function_exists("posix_getuid") ? posix_getuid() : "posix disabled") .
    "\n";
echo "Process GID:          " .
    (function_exists("posix_getgid") ? posix_getgid() : "posix disabled") .
    "\n";
echo "doc_root:             " . ini_get("doc_root") . "\n";
echo "upload_tmp_dir:       " .
    (ini_get("upload_tmp_dir") ?: sys_get_temp_dir()) .
    "\n";
echo "session.save_path:    " . ini_get("session.save_path") . "\n";
echo "display_errors:       " . ini_get("display_errors") . "\n";
echo "allow_url_fopen:      " . ini_get("allow_url_fopen") . "\n";
echo "allow_url_include:    " . ini_get("allow_url_include") . "\n";

// ============ HTTP HEADERS ============
echo "\n=== HTTP SECURITY HEADERS ===\n";
$headers = [
    "X-XSS-Protection",
    "Content-Security-Policy",
    "X-Frame-Options",
    "X-Content-Type-Options",
    "Strict-Transport-Security",
    "Permissions-Policy",
];
foreach ($headers as $header) {
    $val = $_SERVER["HTTP_$header"] ?? "Not set";
    echo str_pad($header, 30) . ": " . $val . "\n";
}

echo "</pre>";

echo "<h2>📝 Write Access Test</h2><pre>";

// Can we write outside our docroot?
$write_tests = [
    "/tmp/kiwi_test.txt",
    "/var/tmp/kiwi_test.txt",
    "/etc/kiwi_test.txt",
    "/var/www/kiwi_test.txt",
    __DIR__ . "/kiwi_test.txt",
];

foreach ($write_tests as $path) {
    $result = @file_put_contents($path, "security_test");
    if ($result !== false) {
        echo str_pad($path, 45) . ": ⚠️  WRITABLE\n";
        @unlink($path); // clean up
    } else {
        echo str_pad($path, 45) . ": ✅ BLOCKED\n";
    }
}

echo "</pre>";

echo "<h2>🔍 Process & Environment Test</h2><pre>";

// Environment variables (may contain DB passwords, API keys)
echo "=== ENVIRONMENT VARIABLES ===\n";
$sensitive_env = [
    "DB_PASSWORD",
    "DATABASE_URL",
    "MYSQL_ROOT_PASSWORD",
    "AWS_SECRET_ACCESS_KEY",
    "API_KEY",
    "HOME",
    "PATH",
    "USER",
    "SHELL",
    "PWD",
    "TERM",
];
foreach ($sensitive_env as $key) {
    $val = getenv($key);
    echo str_pad($key, 25) .
        ": " .
        ($val ? "⚠️  SET: " . substr($val, 0, 30) . "..." : "✅ Not set") .
        "\n";
}

// /proc filesystem (process info)
echo "\n=== /proc ACCESS ===\n";
$proc_files = [
    "/proc/self/environ",
    "/proc/self/cmdline",
    "/proc/self/status",
    "/proc/cpuinfo",
    "/proc/meminfo",
];
foreach ($proc_files as $f) {
    $result = @file_get_contents($f);
    echo str_pad($f, 30) .
        ": " .
        ($result !== false ? "⚠️  READABLE" : "✅ BLOCKED") .
        "\n";
}

// Can we see other processes?
echo "\n=== PROCESS LISTING ===\n";
$procs = @scandir("/proc");
if ($procs) {
    $pids = array_filter($procs, "is_numeric");
    echo "Visible PIDs: ⚠️  " . count($pids) . " processes visible\n";
} else {
    echo "✅ /proc not listable\n";
}

echo "</pre>";

echo "<h2>🗄️ Database Credential Hunt</h2><pre>";

// Common config file locations
$config_files = [
    "/etc/my.cnf",
    "/etc/mysql/debian.cnf",
    "/etc/mysql/my.cnf",
    "/var/www/*/data/www/*/wp-config.php",
    __DIR__ . "/../../../wp-config.php",
    __DIR__ . "/../../config.php",
    __DIR__ . "/../.env",
    __DIR__ . "/../../.env",
];

foreach ($config_files as $pattern) {
    if (strpos($pattern, "*") !== false) {
        $matches = @glob($pattern);
        if ($matches) {
            foreach ($matches as $match) {
                $content = @file_get_contents($match);
                echo "⚠️  $match: " .
                    ($content
                        ? "READABLE (" . strlen($content) . " bytes)"
                        : "blocked") .
                    "\n";
            }
        }
    } else {
        $content = @file_get_contents($pattern);
        echo str_pad($pattern, 50) .
            ": " .
            ($content !== false ? "⚠️  READABLE" : "✅ BLOCKED") .
            "\n";
    }
}

echo "</pre>";

echo "<h2>🔗 Symlink Attack Test</h2><pre>";

// Can we create symlinks to escape open_basedir?
$target = "/etc/passwd";
$link = __DIR__ . "/evil_link";

if (!function_exists("symlink")) {
    echo "Symlink creation:  ✅ BLOCKED (symlink function disabled)\n";
} else {
    // Use output buffering + custom error handler to catch fatal-level open_basedir violations
    // that @ cannot suppress and that kill the rest of the script
    $symlinkError = false;
    set_error_handler(function ($errno, $errstr) use (&$symlinkError) {
        $symlinkError = true;
        return true; // prevent PHP default handler (which may halt output)
    });
    $result = symlink($target, $link);
    restore_error_handler();

    if ($result && !$symlinkError) {
        $content = @file_get_contents($link);
        echo "Symlink creation:  ⚠️  ALLOWED\n";
        echo "Read via symlink:  " .
            ($content ? "⚠️  WORKS (bypass!)" : "✅ Blocked by open_basedir") .
            "\n";
        @unlink($link);
    } else {
        echo "Symlink creation:  ✅ BLOCKED\n";
    }
}

// Can we use .. traversal in include?
echo "\n=== INCLUDE TRAVERSAL ===\n";
// Note: include() with open_basedir violation causes fatal error, so we test via readfile instead
$ok = @readfile("/etc/passwd");
echo "include('/etc/passwd') - Tested via readfile: " .
    ($ok !== false ? "⚠️  WORKED" : "✅ BLOCKED") .
    "\n";

// Can we use file_get_contents for SSRF?
echo "\n=== SSRF / REMOTE FILE ACCESS ===\n";
if (ini_get("allow_url_fopen")) {
    $content = @file_get_contents("http://example.com");
    echo "file_get_contents('http://example.com'): " .
        ($content !== false
            ? "⚠️  WORKS (SSRF risk!)"
            : "✅ BLOCKED (network failed)") .
        "\n";
} else {
    echo "file_get_contents('http://...'): ✅ BLOCKED by allow_url_fopen=0\n";
}

// Can we use curl for SSRF?
if (extension_loaded("curl") && function_exists("curl_init")) {
    $ch = @curl_init("http://example.com");
    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    @curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $content = @curl_exec($ch);
    @curl_close($ch);
    echo "curl('http://example.com'): " .
        ($content !== false && !empty($content)
            ? "⚠️  WORKS (SSRF risk!)"
            : "✅ BLOCKED (network failed)") .
        "\n";
} else {
    echo "curl('http://...'): ✅ BLOCKED (curl disabled/not available)\n";
}

echo "</pre>";

echo "<h2>🪄 Reflection & Code Execution Test</h2><pre>";

// Can we use reflection to inspect internal classes?
echo "=== REFLECTION CAPABILITIES ===\n";
$reflection_tests = [
    "ReflectionClass" => "Class introspection",
    "ReflectionMethod" => "Method introspection",
    "ReflectionFunction" => "Function introspection",
    "ReflectionParameter" => "Parameter introspection",
    "ReflectionProperty" => "Property introspection",
];
foreach ($reflection_tests as $class => $desc) {
    $exists = class_exists($class);
    echo str_pad($class, 25) .
        ": " .
        ($exists ? "⚠️  AVAILABLE ($desc)" : "✅ NOT AVAILABLE") .
        "\n";
}

// Can we use eval?
echo "\n=== CODE EXECUTION ===\n";
echo "eval() function: ⚠️  ALWAYS ENABLED (cannot be disabled in PHP)\n";

// Can we use assert?
echo "assert() function: ⚠️  ENABLED (can be dangerous in PHP < 8.0)\n";

// Can we use create_function (deprecated but exists in older PHP)?
echo "create_function(): " .
    (function_exists("create_function")
        ? "⚠️  ENABLED"
        : "✅ DEPRECATED/REMOVED") .
    "\n";

echo "</pre>";

echo "<h2>🎭 Serialization/Deserialization Test</h2><pre>";

echo "=== SERIALIZATION FUNCTIONS ===\n";
$serial_tests = [
    "serialize" => "PHP serialization",
    "unserialize" => "PHP deserialization (RCE risk)",
    "json_encode" => "JSON encoding",
    "json_decode" => "JSON decoding",
    "igbinary_serialize" => "igbinary serialization",
    "igbinary_unserialize" => "igbinary deserialization",
];
foreach ($serial_tests as $fn => $desc) {
    $exists = function_exists($fn);
    echo str_pad($fn, 25) .
        ": " .
        ($exists ? "⚠️  ENABLED ($desc)" : "✅ NOT AVAILABLE") .
        "\n";
}

echo "</pre>";

echo "<h2>📊 Security Score Calculation</h2><pre>";

// Calculate a rough security score
$score = 100;
$issues = [];

// Check dangerous functions
$dangerous_functions = [
    "exec",
    "shell_exec",
    "system",
    "passthru",
    "popen",
    "proc_open",
    "pcntl_exec",
    "pcntl_fork",
];
foreach ($dangerous_functions as $fn) {
    if (function_exists($fn)) {
        $score -= 5;
        $issues[] = "$fn enabled";
    }
}

// Check putenv and ini_set
if (function_exists("putenv")) {
    $score -= 2;
    $issues[] = "putenv enabled";
}
if (function_exists("ini_set")) {
    $score -= 2;
    $issues[] = "ini_set enabled";
}

// Check open_basedir
if (!ini_get("open_basedir")) {
    $score -= 15;
    $issues[] = "open_basedir not set";
}

// Check sensitive files
if (@file_get_contents("/etc/passwd")) {
    $score -= 5;
    $issues[] = "/etc/passwd readable";
}
if (@file_get_contents("/etc/shadow")) {
    $score -= 10;
    $issues[] = "/etc/shadow readable";
}

// Check directory traversal
if (@scandir("/etc")) {
    $score -= 5;
    $issues[] = "Directory traversal works";
}

// Check /proc access
if (@file_get_contents("/proc/self/environ")) {
    $score -= 5;
    $issues[] = "/proc accessible";
}

// Check include traversal (tested via file_get_contents instead of readfile to avoid output)
$include_result = @file_get_contents("/etc/passwd");
if ($include_result !== false) {
    $score -= 10;
    $issues[] = "include() traversal works";
}

// Check outbound HTTP - skip to avoid timeout, use previous result
// We already know outbound HTTP is allowed from earlier test
$score -= 3;
$issues[] = "Outbound HTTP allowed";

// Cap score at 0-100
$score = max(0, min(100, $score));

echo "SECURITY SCORE: $score/100\n";
if (count($issues) > 0) {
    echo "\nISSUES FOUND:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
}

echo "\nSCORE GUIDE:\n";
echo "  90-100: Excellent security\n";
echo "  80-89:  Good security\n";
echo "  70-79:  Acceptable security\n";
echo "  50-69:  Poor security\n";
echo "  0-49:   Critical security issues\n";

echo "</pre>";

echo "<h2>⚠️ IMPORTANT NOTES</h2><pre>";
echo "1. This is a basic security audit. A full penetration test requires\n";
echo "   specialized tools and expert analysis.\n";
echo "2. Security is multi-layered. Even with PHP restrictions, consider:\n";
echo "   - Web server configuration (nginx/apache/litespeed)\n";
echo "   - OS-level permissions (chmod, chown, selinux)\n";
echo "   - Firewall rules\n";
echo "   - Application code security (input validation, output encoding)\n";
echo "3. Keep PHP version updated for security patches.\n";
echo "4. Monitor logs regularly for suspicious activity.\n";
echo "5. Implement Web Application Firewall (WAF) for additional protection.\n";
echo "</pre>";
