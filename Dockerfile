FROM php:8.2-fpm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd -g $GID appgroup && \
    useradd -u $UID -g appgroup -m appuser

USER appuser

WORKDIR /var/www/tezicare-api.tezi.co.ke

EXPOSE 9000

CMD ["php-fpm"]