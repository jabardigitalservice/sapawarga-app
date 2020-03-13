FROM composer:1.10 as vendor
COPY . /app

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM php:7.4-fpm-alpine

RUN apk add --no-cache \
        freetype \
        freetype-dev \
        libjpeg-turbo \
        libjpeg-turbo-dev \
        libpng \
        libpng-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql gd

WORKDIR /srv

COPY --chown=www-data:www-data . /srv
COPY --from=vendor --chown=www-data:www-data /app/vendor/ /srv/vendor/
