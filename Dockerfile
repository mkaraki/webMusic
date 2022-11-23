# syntax = docker/dockerfile:1.4.0
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

COPY server/.htaccess /app/
COPY server/index.php /app/
COPY server/_config.php /app/
COPY server/init.db/* /app/init.db/
COPY server/composer.* /app/
COPY server/api /app/api
COPY server/ui /app/ui
COPY server/bin/*.php /app/bin/

RUN composer install --no-dev


FROM php:apache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd mysqli
RUN pecl install apcu \
    && docker-php-ext-install opcache \
    && docker-php-ext-enable apcu

RUN <<EOF cat >> $PHP_INI_DIR/conf.d/apcu.ini
[apcu]
apc.enable=1
apc.enable_cli=1
EOF

RUN a2enmod rewrite

RUN <<EOF cat >> /etc/apache2/conf-enabled/extenv.conf
SetEnv DB_HOST \${DB_HOST}
SetEnv DB_PORT \${DB_PORT}
SetEnv DB_USER \${DB_USER}
SetEnv DB_PASS \${DB_PASS}
SetEnv DB_NAME \${DB_NAME}
SetEnv CORS_ORIGIN \${CORS_ORIGIN}
EOF

COPY --from=require-server /app /var/www/html
COPY --from=build-webapp /app/dist /var/www/html/public
RUN mkdir /var/www/html/cache