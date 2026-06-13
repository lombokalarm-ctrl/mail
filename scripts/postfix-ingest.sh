#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="${APLI_MAIL_PROJECT_DIR:-/var/www/email}"
COMPOSE_FILE="${APLI_MAIL_COMPOSE_FILE:-$PROJECT_DIR/docker-compose.yml}"

cd "$PROJECT_DIR"

exec /usr/bin/docker compose -f "$COMPOSE_FILE" exec -T app php artisan mail:ingest
