# Use the official PHP image as the base image
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    supervisor

# Install and configure extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set up Laravel application
WORKDIR /var/www
COPY . .

# Install Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

# Copy the Supervisor configuration file for Laravel Reverb
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Start Supervisor and PHP-FPM
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
