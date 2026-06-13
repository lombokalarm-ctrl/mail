FROM node:24-bookworm AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY postcss.config.js ./
COPY tailwind.config.js ./
RUN npm run build

FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

FROM php:8.4-fpm-bookworm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    $PHPIZE_DEPS \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    supervisor \
    && docker-php-ext-install pdo_pgsql bcmath intl zip pcntl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=frontend /app/public/build ./public/build
COPY --from=frontend /app/public/build /opt/apli-public-build
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN php artisan package:discover --ansi

RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /opt/apli-public-build /var/www/html/public/build \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
