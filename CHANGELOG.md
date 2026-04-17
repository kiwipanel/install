# Changelog

## v0.8.1 (2026-04-17)

- feat: update database schema, update version
- Merge pull request #159 from kiwipanel/feature/backup
- feat: add recidive to fail2ban
- feat: install fail2ban, add the scrollbar to audilot
- feat: installing fail2ban
- fix code quality-context scope
- feat: backup locally
- release v0.8.0
## v0.8.0 (2026-04-17)

- Merge pull request #157 from kiwipanel/feature/setting
- fix test
- feat: cron
- feat: network_storage_process
- feat: Sysctl Curated Tuning
- update readme
- chore: fmt
- feat: reduce time loading to setting/server
- fix test case
- feat: fail2ban
- feat: hardening
- feat: firewall
- feat: implementing update system
- feat: setting timezone, swap for vps
- fix golangci-lint
- fix fmt
- feat: setting-> server page
- Merge pull request #155 from kiwipanel/feature/global
- feat: render beautifully phpinfo()
- fix context-watchdog
- feat: saving new php setting will update automatically
- Merge pull request #154 from kiwipanel/feature/php
- feat: calculate amount of opcache based on sites per php version
- feat: setting a looser opcache
- fix test case php setting
- feat: setting opcache with threshold
- fix: the hyperlink when configure
- feat: showing alert when setting opcache memory wrong
- feat: auto-fix php error or show error
- feat: self-restart php
- feat: self-healing php
- fix: watchdog php correctly
- feat: using agent to read php log
- feat: watch detect php
- release v0.7.9
## v0.7.9

- update db
- Merge pull request #153 from kiwipanel/feature/watching
- feat: php log globally
- feat: update the heal-watching, reduce the inteval to 10 seconds
- feat: new sql schema
- fix: showing disk type
- feat: reset uptime
- feat: tweak watch
- feat: add test cases for watching feature
- release v0.7.8
## v0.7.8

- Merge pull request #152 from kiwipanel/feature/setting
- feat: add the service health
- Merge pull request #150 from kiwipanel/feature/phpset
- chore: formating
- feat: add more info to the dashboard
- fix test case
- feat: add health check and self-fix
- feat: add self-healing
- fix - setting opache
- release v0.7.7
## v0.7.7

- Merge pull request #148 from kiwipanel/feature/watch
- feat: update readme about the self-healing feature
- feat: added self-heal system using systemd
- feat: remove option to set opache value
- test: fix test
- fix: setting opache memory globally, not each site
- feat: remove notification, clean up recent activity
- feat: tracking editing profile
- hotfix: release on public should be identical to the private one
- release v0.7.6
## v0.7.6

- Merge pull request #147 from kiwipanel/feature/site 
- version v0.7.6
- test: added thorough test cases for logging: 
- fix: httpauth now work even the location is not created 
- feat: tracking user events in the /events route
- update install 
- Merge pull request #146 from kiwipanel/feature/settings 
- feat: update readme.md 
- fix: the route for re-activating website should be POST 
- feat: update the recent activities menu 
- feat: update the security audit 
- feat: show cursor when selecting log file 
- feat: setting chroot as enabled by default 
- release v0.7.5 

## v0.7.5
  - Merge pull request from kiwipanel/feature/passcode
  - fix golangci - G120, G122
  - fix golangci (1b1f5cb)
  - feat: add new testing tools
  - feat: update the metric with more info, update workflow
  - feat: add more metric info
  - update schema (1a01f25)
  - feat: add the schema sql for license
  - release v0.7.4
