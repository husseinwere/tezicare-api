FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    && apt-get clean

RUN git config --global --add safe.directory '*'
