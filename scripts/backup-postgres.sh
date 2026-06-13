#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="${APLI_MAIL_PROJECT_DIR:-/var/www/email}"
BACKUP_DIR="${APLI_MAIL_BACKUP_DIR:-/var/backups/apli-mail}"
RETENTION_DAYS="${APLI_MAIL_BACKUP_RETENTION_DAYS:-7}"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$BACKUP_DIR"
cd "$PROJECT_DIR"

if [ -f "$PROJECT_DIR/.env" ]; then
  set -a
  # shellcheck disable=SC1091
  . "$PROJECT_DIR/.env"
  set +a
fi

docker compose exec -T postgres pg_dump \
  -U "${DB_USERNAME:-apli}" \
  -d "${DB_DATABASE:-apli_mail}" \
  --clean \
  --if-exists \
  --no-owner \
  --no-privileges \
  | gzip > "$BACKUP_DIR/apli-mail-${TIMESTAMP}.sql.gz"

find "$BACKUP_DIR" -type f -name 'apli-mail-*.sql.gz' -mtime +"$RETENTION_DAYS" -delete
