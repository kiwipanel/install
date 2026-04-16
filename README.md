# KiwiPanel

![License](https://img.shields.io/badge/license-BSL-blue)
![Go Version](https://img.shields.io/badge/go-1.24+-00ADD8)
![Status](https://img.shields.io/badge/status-pre--alpha-red)
![Version](https://img.shields.io/badge/version-0.6.4-green)
[![Tests](https://github.com/kiwipanel/kiwipanel/actions/workflows/tests.yml/badge.svg)](https://github.com/kiwipanel/kiwipanel/actions/workflows/tests.yml)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/b1233420d6264734b6c79190bf03c354)](https://app.codacy.com/gh/kiwipanel/kiwipanel/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![codecov](https://codecov.io/github/kiwipanel/kiwipanel/graph/badge.svg?token=LRQW6YYSOC)](https://codecov.io/github/kiwipanel/kiwipanel)

<p align="center">
  <img src="screenshot.png" alt="KiwiPanel Dashboard" width="600">
</p>

## KiwiPanel

⚠️ WARNING: PRE-ALPHA RELEASE - DO NOT DEPLOY TO PRODUCTION

KiwiPanel is a lightweight, open-source server control panel for managing a **LOMP stack**  
(Linux, OpenLiteSpeed, MariaDB, PHP).

It focuses on simplicity, transparency, and sane defaults — without bloat or lock-in.

### Key Features

- **Website Management** - Full vhost lifecycle (create, suspend, activate, delete) with OpenLiteSpeed, per-site settings (general, security, static content, resource limits, HTTP auth, redirects)
- **File Manager** - In-browser file manager with Ace Editor, upload/download, compress/decompress, chmod, search
- **Terminal** - Per-website jailed terminal with 5-layer defense-in-depth sandbox (systemd, UID isolation, command guard, PATH restriction, rcfile)
- **SSL/TLS** - Free Let's Encrypt certificates with auto-renewal, custom certificate upload, ZeroSSL and Buypass support
- **Domains** - Domain and subdomain configuration per website
- **Database** - MariaDB database and user administration
- **Logs** - Real-time access and error log viewer with statistics
- **PHP** - Per-site PHP version selection (8.2–8.5) with suEXEC + lsphp pools
- **Backups** - Backup scheduling and restoration
- **Cron** - Scheduled task management
- **FTP** - FTP account management
- **Plans & Quotas** - Resource plans with quota enforcement (websites, domains, databases, disk, feature toggles)
- **User Management** - Role-based access control with isolated Linux accounts, auto-generated credentials for new clients
- **Dashboard** - Real-time VPS performance metrics, service health checks, service control (start/stop/restart)
- **Security** - SSH key management, SSH hardening (port, password auth), firewall rules (UFW/firewalld) with auto-rollback, Fail2ban management, kernel hardening, demo mode
- **Server Settings** - Hostname, timezone, swap management, system updates with async loading
- **CLI** - Comprehensive `kiwipanel` CLI with system checks, diagnostics, service management, and hardening

### Built for Developers & VPS Users

- Developers who want full control and visibility
- VPS users who prefer lightweight tools
- Sysadmins who value clean configs and reproducibility

### Early Development Notice

This project is still in **early development** and evolving rapidly. APIs, features, and internal design may change at any time.

- Expect breaking changes
- Expect missing features
- Expect rough edges

Feedback, issue reports, and contributions are welcome and encouraged. KiwiPanel is built in the open, with the goal of growing into a dependable, no-nonsense control panel that respects both the server and the user.

### Supported Operating Systems

KiwiPanel supports non-EOL Linux distributions with **systemd 249+**, officially supported by OpenLiteSpeed.

- Debian 12, 13
- Ubuntu LTS 22.04, 24.04
- Rocky Linux 9, 10
- AlmaLinux 9, 10
- RHEL 9 and compatible derivatives

> **Note:** Debian 11, AlmaLinux 8, Rocky Linux 8, and RHEL 8 are **not supported** — they ship with systemd < 249 which lacks required security features for terminal sandboxing.

CentOS Stream and EOL distributions are not supported.

## KiwiPanel Installation

### Prerequisites

- Fresh VPS with supported OS (systemd 249+ required)
- Minimum 512MB RAM (1GB is recommended)
- Minimum 1GB disk space
- Root access

### Install

**Option 1:**
```bash
bash <(curl -fsSL https://raw.githubusercontent.com/kiwipanel/install/main/install)
```

**Option 2:**
```bash
curl -sLO https://raw.githubusercontent.com/kiwipanel/install/main/install && chmod +x install && sudo bash install
```

##### Port 8443:
On some cloud service providers such as Amazon Lightsail or Oracle, you have to manually open the port 8443 inside their control dashboards.

## Roadmap

The roadmap below reflects the current direction, with progress status based on actual code implementation. Priorities may shift based on real-world usage and community feedback.

### Phase 0 — Foundation (Pre-Alpha) ✅ COMPLETE
**Goal:** Establish a clean, inspectable core with minimal abstraction.

- [x] Installer bootstrap for supported Linux distributions
- [x] Go-based modular backend architecture with clean separation of concerns
- [x] CLI framework (`kiwipanel`) with comprehensive system-level operations
- [x] SQLite-based local state with SQLC for type-safe operations
- [x] Thorough system inspection (CPU, memory, disk, OS...) with `kiwipanel check`
- [x] OpenLiteSpeed + MariaDB + PHP stack provisioning
- [x] Clear separation between panel logic and system tooling
- [x] Internal logging and structured error handling
- [x] Kernel hardening via `kiwipanel harden kernel` with {check, apply, rollback}
- [x] Agent system for privileged operations with proper isolation

---

### Phase 1 — Core Panel Features (Alpha) ✅ COMPLETE
**Goal:** Make KiwiPanel usable for real servers with limited scope.

- [x] Rescuing and diagnosing tool (`kiwi` command line using bash)
- [x] Web-based self-update functionality
- [x] Web UI authentication with session management and role-based access control
- [x] Live dashboard with real-time VPS performance metrics
- [x] Agent system for privileged operations with separate binary
- [x] Service management (start/stop/reload)
  - OpenLiteSpeed
  - MariaDB
  - PHP
- [x] User management with isolated accounts, permissions, levels, and roles
  - Auto-generated credentials for new client users (crypto/rand, 16+ char passwords)
  - Client home directory creation with per-user UID (10000+)
  - PAM limits enforcement per user
- [x] Comprehensive log viewer (OLS, MariaDB, system, website access/error logs with statistics)
- [x] Full website management:
  - Virtual host creation and management
  - Document root configuration
  - PHP version selection (8.2, 8.3, 8.4, 8.5)
  - Website suspension/activation
  - Domain configuration
- [x] Database management (create/delete users & databases)
- [x] File manager with Ace Editor, upload (tus protocol), download, compress/decompress, chmod, search
- [x] Safe defaults for permissions and filesystem layout with per-website Linux users
- [x] Non-destructive config generation (no silent overwrites)
- [x] Per-website jailed terminal with systemd sandbox and Linux user isolation
- [x] PHP version per site with suEXEC + lsphp pools
- [x] SSL/TLS management:
  - Free Let's Encrypt certificates (automated issuance + renewal)
  - ZeroSSL and Buypass certificate providers
  - Custom certificate upload
  - Certificate removal and renewal
  - Force HTTPS toggle
  - OLS virtual host SSL configuration
- [x] Plans & quotas system with enforcement (websites, domains, databases, disk, feature toggles)
- [x] Demo mode (read-only admin account for showcasing)

---

### Phase 2 — Security & Hardening (Current) 🟡 IN PROGRESS
**Goal:** Secure-by-default without hiding the system.

- [x] Security audit via `kiwipanel check` command
- [x] Firewall management (UFW for Debian family, firewalld for RHEL family) with preview + auto-rollback
- [x] SSH key management
- [x] Website terminal sandboxing (5-layer defense-in-depth):
  - [x] systemd transient service sandbox (`ProtectSystem=strict`, `ProtectHome=tmpfs`, `PrivateTmp`, `NoNewPrivileges`, `MemoryMax=256M`, `CPUQuota=50%`, `TasksMax=50`)
  - [x] PID namespace isolation (`PrivatePIDs=yes` on systemd v256+)
  - [x] Real-time command guard (35+ regex patterns blocking destructive commands)
  - [x] Curated PATH restriction (89 allowed commands, shells excluded)
  - [x] Bash rcfile with `cd` override, DEBUG trap, and readonly environment
  - [x] PAM limits per-user (`nproc=50`, `nofile=1024`)
  - [x] UID/GID isolation (per-website Linux user, uid >= 10000)
- [x] TLS management (Let's Encrypt + ZeroSSL + Buypass automation)
- [x] Website settings management:
  - [x] General settings (website name, status toggle)
  - [x] Security settings (directory listing, hotlink protection)
  - [x] Static content settings (gzip compression, cache expiry)
  - [x] Resource limits (max connections, timeout)
  - [x] Index files configuration
  - [x] HTTP Authorization (htpasswd-style directory protection CRUD)
  - [x] URL Redirects management (301/302 rules CRUD)
  - [x] Danger zone (suspend/unsuspend, delete)
- [x] PHP security hardening:
  - [x] Per-website `open_basedir`, `disable_functions` presets
  - [x] Global PHP security defaults (`expose_php`, `allow_url_include`, session hardening)
  - [x] Admin-configurable PHP option allowlist
  - [x] PHP config audit trail with before/after snapshots
  - [x] **PHP suEXEC & chroot filesystem jail** (kernel-level isolation)
    - [x] `chrootPath` + `chrootMode 2` in vhost templates
    - [x] Chroot jail setup (`dev/`, `etc/`, `tmp/` directories)
    - [x] suEXEC verification agent endpoint
    - [x] Global toggle in Settings → PHP Security
- [x] Fail2ban integration with jail management and banned IP overview
- [x] SSH hardening (port change, password auth toggle, authorized key management) with rollback timer
- [x] System/OS settings (hostname, timezone, swap management, system updates)
- [ ] SELinux/AppArmor compatibility
- [ ] Port exposure and service visibility controls
- [ ] Explicit warnings for unsafe configurations


---

### Phase 3 — Core Reliability & Operations ⬜ PLANNED
**Goal:** Make KiwiPanel production-trustworthy with automated recovery, consistent backups, and resource enforcement.

- [x] Idempotent installer with step runner, verify-before-skip, and resume support (tested on Ubuntu 22/24, Debian 12/13, AlmaLinux 9/10, Rocky 9)
- [ ] Agent protocol hardening — strict typed RPC, no generic command execution
- [x] Self-healing systemd drop-in overrides for critical services (lsws, mariadb, redis) — automatic crash recovery even when panel/agent are down
- [x] Service watchdog via systemd DBus subscription with circuit breaker and auto-heal
- [ ] Local backups with consistency guarantees (single-transaction dumps, double-rsync) and restore isolation via staging
- [ ] Disk + resource limit enforcement via Linux quotas (`setquota`/`repquota`)
- [ ] Full domain management (DNS zones, subdomains, aliases, SSL auto-provisioning)
- [ ] Scriptable actions with JSON/stdout-friendly output
- [ ] Filesystem quotas implementation

---

### Phase 4 — Security Hardening & Isolation ⬜ PLANNED
**Goal:** Harden multi-tenant isolation and detect configuration anomalies.

- [ ] Config drift detection with normalized hashing and ownership model
- [ ] CageFS Tier 1 (systemd-native per-user filesystem isolation)
- [ ] WAF integration (Coraza for panel, OLS ModSecurity for hosted sites)
- [ ] Automated security scanning (ClamAV, malware detection)
- [ ] SELinux/AppArmor compatibility

---

### Phase 5 — Migration & Growth ⬜ PLANNED
**Goal:** Unlock new users by making KiwiPanel the obvious migration target.

- [ ] DirectAdmin migration adapter with pre-flight compatibility analysis
- [ ] Migration framework with `MigrationSource` interface and dry-run mode
- [ ] Per-site traffic accounting via streaming log aggregation
- [ ] CLI + API polish (JSON API layer, OpenAPI spec, scripting support)

---

### Phase 6 — Premium & Ecosystem ⬜ PLANNED
**Goal:** Features that justify paid tiers and grow the ecosystem.

- [ ] Remote backups (S3, B2, SFTP) — builds on local backup infrastructure
- [ ] Rootless Docker addon with per-user containers
- [ ] Performance tuning suite (jemalloc, OPcache presets, MariaDB auto-tuning)
- [ ] One-click app installer (WordPress, Laravel, Node.js)
- [ ] Observability timeline (unified event view across all features)
- [ ] External monitoring integration (Prometheus-compatible metrics)
- [ ] Webhook notifications (Slack, Discord, email on events)

> 📋 See [plans/ideas.md](plans/ideas.md) for the full 31-item feature roadmap with implementation details, design decisions, and strategic rationale.

---

### 🤝 Contributing
We welcome contributions! However, because KiwiPanel uses a dual-licensing model (Community & Commercial), we need to ensure we have the legal right to include your code in both versions.

By submitting a Pull Request, you agree that:
1.  You own the rights to the code you are contributing.
2.  You grant the project owner (Vuong Nguyen) an unrestricted, perpetual right to use, modify, and distribute your contribution as part of KiwiPanel (including in the Commercial License version).

See [CONTRIBUTING.md](CONTRIBUTING.md) for the full Contributor License Agreement (CLA).

## License Summary

KiwiPanel is **source-available** software released under a **Business Source License (BSL-style)**.

### ✅ Free to use if you are:
- An individual (personal, educational, or hobby use)
- A non-profit organization
- A business with **less than $100,000 USD in annual revenue**

### ❌ You may NOT:
- Redistribute or resell the software
- Offer KiwiPanel as a hosted or managed service (SaaS / PaaS)
- Create or distribute forks
- Relicense the code under GPL, AGPL, or any other open-source license

### 💼 Commercial Use
If your organization exceeds the free-use limits, a **commercial license is required**.

📄 See the full [LICENSE](./LICENSE.md) for details.
