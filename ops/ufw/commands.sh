#!/usr/bin/env bash
set -euo pipefail

ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw deny 8080/tcp
ufw deny 5432/tcp
ufw deny 6379/tcp
ufw --force enable
ufw status verbose
