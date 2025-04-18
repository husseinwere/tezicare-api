FROM php:8.1-fpm

WORKDIR /var/www/tezicare-api.tezi.co.ke

RUN apt-get update && apt-get install -y git unzip libzip-dev && docker-php-ext-install zip

RUN git config --global --add safe.directory '*'