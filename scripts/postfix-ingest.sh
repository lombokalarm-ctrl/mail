#!/usr/bin/env bash
set -euo pipefail

CONTAINER_NAME="${APLI_MAIL_APP_CONTAINER:-apli-mail-app}"

exec /usr/bin/docker exec -i "$CONTAINER_NAME" php artisan mail:ingest
