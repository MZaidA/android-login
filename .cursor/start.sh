#!/usr/bin/env bash
# Per-boot reconciliation: bring up MariaDB and ensure the schema exists.
# The PHP dev server itself runs as a visible terminal (see environment.json).
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "==> Starting MariaDB"
sudo service mariadb start || true
for _ in $(seq 1 30); do
  sudo mariadb -e "SELECT 1" >/dev/null 2>&1 && break
  sleep 1
done

# Ensure database + table exist (idempotent; covers a fresh data dir).
sudo mariadb < "$REPO_ROOT/HTTPLogin/schema.sql" || true

echo "==> MariaDB ready"
