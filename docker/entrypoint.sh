#!/usr/bin/env sh
set -e

cd /var/www/html

mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
mkdir -p public/build
rm -rf public/build/*
cp -a /opt/apli-public-build/. public/build/
chown -R www-data:www-data storage bootstrap/cache

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
php artisan view:clear >/dev/null 2>&1 || true

exec "$@"
