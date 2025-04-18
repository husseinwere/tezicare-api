FROM php:8.1-fpm

ARG UID=1000
ARG GID=1000

RUN groupadd -g $GID appgroup && \
    useradd -u $UID -g appgroup -m appuser

USER appuser

WORKDIR /var/www/tezicare-api.tezi.co.ke