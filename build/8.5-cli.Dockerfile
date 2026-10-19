# Use an official PHP runtime as a base image
FROM php:8.5-cli

# basic update
RUN apt-get update && \
    apt-get install --yes --force-yes \
    cron openssl

# installing the docker php extensions installer
RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions

# PHP Configuration
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN install-php-extensions  gettext iconv intl  tidy zip sockets
RUN install-php-extensions  pgsql mysqli
RUN install-php-extensions  pdo_mysql pdo_pgsql
RUN install-php-extensions  xdebug
RUN install-php-extensions @composer
EXPOSE 80



#COPY php.ini /usr/local/etc/php/


