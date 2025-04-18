FROM php:8.1-fpm

ARG UID=1000
ARG GID=1000

RUN groupadd -g $GID appgroup && \
    useradd -u $UID -g appgroup -m appuser

USER appuser

WORKDIR /var/www/tezicare-api.tezi.co.ke

RUN apt-get update && apt-get install -y git unzip libzip-dev && docker-php-ext-install zip

RUN git config --global --add safe.directory '*'