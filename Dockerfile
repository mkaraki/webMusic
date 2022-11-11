FROM node:18-bullseye AS build-webapp

RUN npm install -g pnpm

COPY client /app

WORKDIR /app

RUN pnpm install --frozen-lockfile
RUN pnpm build


FROM composer AS require-server

WORKDIR /app

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd

RUN touch /app/_config.php
COPY server/.htaccess /app/
COPY server/index.php /app/
COPY server/composer.* /app/
COPY server/api /app/api

RUN composer install --no-dev


FROM php:apache

RUN a2enmod rewrite
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd mysqli

COPY --from=require-server /app /var/www/html
COPY --from=build-webapp /app/dist /var/www/html/public