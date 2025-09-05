# Build stage
FROM composer:2 AS composer-stage
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Production stage
FROM php:8.1-fpm-alpine

# Install essential dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libzip-dev \
    icu-dev \
    && docker-php-ext-install pdo_mysql mysqli gd zip intl

# Copy vendor dependencies from composer stage
COPY --from=composer-stage /app/vendor /var/www/html/vendor

# Copy application code
COPY . /var/www/html

# Set proper permissions (readable by nginx user)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/writable

# Copy configuration files
COPY setup/nginx.conf /etc/nginx/http.d/default.conf
COPY setup/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create supervisor directory
RUN mkdir -p /etc/supervisor/conf.d

# Set working directory
WORKDIR /var/www/html

# Expose HTTP port
EXPOSE 80

# Start supervisor (manages both nginx and php-fpm)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]