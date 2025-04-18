FROM php:8.1-fpm

WORKDIR /var/www/tezicare-api.tezi.co.ke

RUN git config --global --add safe.directory /var/www/tezicare-api.tezi.co.ke