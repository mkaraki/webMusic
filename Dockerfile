# syntax = docker/dockerfile:1.4.0
FROM composer AS require-server

WORKDIR /app

COPY server/composer.* /app/

RUN composer install --no-dev --ignore-platform-reqs


FROM node:18-bullseye AS build-webapp

RUN npm install -g pnpm

COPY client /app

WORKDIR /app

RUN pnpm install --dev
RUN pnpm install vue-tsc
RUN pnpm build


FROM php:apache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
ENV IPE_GD_WITHOUTAVIF=1
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

COPY --from=require-server /app/vendor /var/www/html/vendor
COPY --from=build-webapp /app/dist /var/www/html/app

COPY server/.htaccess /var/www/html/
COPY server/index.php /var/www/html/
COPY server/_config.php /var/www/html/
COPY server/init.db/* /var/www/html/init.db/
COPY server/api /var/www/html/api/
COPY server/ui /var/www/html/ui/
COPY server/bin/*.php /var/www/html/bin/

RUN mkdir /var/www/html/cache