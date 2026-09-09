# KiwiPanel

![License](https://img.shields.io/badge/license-BSL-blue)
![Go Version](https://img.shields.io/badge/go-1.24+-00ADD8)
![Status](https://img.shields.io/badge/status-pre--alpha-red)
![Version](https://img.shields.io/badge/version-0.8.91-green)
[![Tests](https://github.com/kiwipanel/kiwipanel/actions/workflows/tests.yml/badge.svg)](https://github.com/kiwipanel/kiwipanel/actions/workflows/tests.yml)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/b1233420d6264734b6c79190bf03c354)](https://app.codacy.com/gh/kiwipanel/kiwipanel/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

<!--<p align="center">
  <img src="screenshot.png" alt="KiwiPanel Dashboard" width="600">
</p>-->

## KiwiPanel

⚠️ WARNING: PRE-ALPHA RELEASE - DO NOT DEPLOY TO PRODUCTION

KiwiPanel is a lightweight, open-source server control panel for managing a **LOMP stack**  
(Linux, OpenLiteSpeed, MariaDB, PHP).

It focuses on simplicity, transparency, and sane defaults - without bloat or lock-in.

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
- **System Management** - Process viewer with kill capability and blocklist protection, open ports viewer (TCP/UDP) with auto-refresh, per-mount and per-user disk usage breakdown with threshold warnings, scheduled reboot with systemd timers, NTP time sync status and server configuration, DNS resolver configuration with Cloudflare/Google/Quad9 presets, system-wide cron job viewer with human-readable schedules, sysctl kernel parameter tuning with presets, OS-level security audit log (SSH/sudo/auth events from journalctl)
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
