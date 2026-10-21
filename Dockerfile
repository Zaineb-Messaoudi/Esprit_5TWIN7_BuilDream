# syntax=docker/dockerfile:1.7

FROM composer:2 AS php-dependencies
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

FROM node:22-alpine AS frontend-assets
WORKDIR /var/www/html
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js .
RUN npm run build

FROM php:8.3-fpm-bookworm AS application
WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libzip-dev \
        libssl-dev \
        unzip \
        curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl mbstring opcache pcntl pdo_mysql sockets zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=php-dependencies /var/www/html/vendor ./vendor
COPY . .
COPY --from=frontend-assets /var/www/html/public/build ./public/build

RUN rm -f bootstrap/cache/*.php \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
    bootstrap/cache \
    && mkdir -p storage/app/public \
    && ln -s ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rw storage bootstrap/cache

USER www-data
EXPOSE 9000 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=5 \
    CMD php-fpm -t || exit 1

CMD ["php-fpm", "-F"]
