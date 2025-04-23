FROM php:8.2-fpm

ARG UID=1000
ARG GID=1000

# Install required dependencies
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

# Copy composer from the composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create a user and group with specified UID and GID
RUN groupadd -g $GID appgroup && \
    useradd -u $UID -g appgroup -m appuser

# Switch to appuser for running the container
USER appuser

# Set working directory to the app directory
WORKDIR /var/www/tezicare-api.tezi.co.ke

# Copy custom php.ini configuration file
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini


# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM service
CMD ["php-fpm"]
