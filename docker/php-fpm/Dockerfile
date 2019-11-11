FROM composer:1.9 as vendor
COPY . /app

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM php:7.3-fpm-alpine

RUN apk add --no-cache \
        freetype \
        freetype-dev \
        libjpeg-turbo \
        libjpeg-turbo-dev \
        libpng \
        libpng-dev && \
    docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ && \
    docker-php-ext-install pdo pdo_mysql gd

WORKDIR /srv

COPY --chown=www-data:www-data . /srv
COPY --from=vendor --chown=www-data:www-data /app/vendor/ /srv/vendor/
