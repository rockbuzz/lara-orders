FROM phpdockerio/php74-fpm:latest

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get -y update \
    && apt-get -y --no-install-recommends install php7.4-sqlite3 php7.4-pcov\
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www