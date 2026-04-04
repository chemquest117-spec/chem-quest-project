# -----------------------------
# Stage 1: Build frontend
# -----------------------------
FROM node:20-alpine AS frontend

WORKDIR /app

# Copy package files first for better caching
COPY package*.json ./
RUN npm ci

# Copy remaining frontend files and build
COPY . .
RUN npm run build


# -----------------------------
# Stage 2: PHP + Apache
# -----------------------------
FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
     git \
     curl \
     zip \
     unzip \
     libpq-dev \
     libzip-dev \
     && docker-php-ext-install pdo pdo_pgsql zip bcmath \
     && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy app code later
COPY . .

# Copy built frontend assets
COPY --from=frontend /app/public/build ./public/build

# Cache Laravel config, routes, and views
# RUN php artisan config:cache && \
#      php artisan route:cache && \
#      php artisan view:cache || true

# Create necessary framework directories (if excluded by .dockerignore) and fix permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
     && mkdir -p /var/www/html/storage/framework/views \
     && mkdir -p /var/www/html/storage/framework/sessions \
     && mkdir -p /var/www/html/bootstrap/cache \
     && chown -R www-data:www-data /var/www/html \
     && chmod -R 755 /var/www/html/storage \
     && chmod -R 755 /var/www/html/bootstrap/cache

# Set Apache public root
RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
     /etc/apache2/sites-available/000-default.conf

# Add this anywhere in Stage 2 to see current environment variables
RUN env


# Create entrypoint script that runs migrations at startup (not build time)
RUN echo '#!/bin/bash\necho "DEBUG: CURRENT_DB_CONNECTION: $DB_CONNECTION"\nenv\nphp artisan config:clear\nphp artisan route:clear\nphp artisan view:clear\nphp artisan optimize:clear\nphp artisan cache:clear\nphp artisan migrate --force\napache2-foreground' > /entrypoint.sh \
     && chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]