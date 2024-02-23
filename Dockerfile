FROM php:8.2-apache

RUN apt-get update && apt install -y \
    git zip unzip libpng-dev \
    libzip-dev mariadb-client

RUN docker-php-ext-install pdo pdo_mysql zip gd mysqli

WORKDIR /var/www

COPY . /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN mkdir -p /var/www/var/cache/dev/ \
    && chown -R www-data:www-data /var/www/var/cache/dev/ \
    && mkdir -p /var/www/var/log/ \
    && chown -R www-data:www-data /var/www/var/log/

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-scripts --no-autoloader

USER root

COPY ./.apache/vhosts.conf /etc/apache2/sites-available/000-default.conf

CMD ["apachectl", "-D", "FOREGROUND"]
