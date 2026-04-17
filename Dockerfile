# -----------------------------
# Stage 1: Build frontend
# -----------------------------
FROM node:20-alpine AS frontend

WORKDIR /app

# Copy package files first for better caching
COPY package*.json ./
RUN npm ci

# Copy remaining frontend files and build
ARG VITE_FIREBASE_API_KEY
ARG VITE_FIREBASE_AUTH_DOMAIN
ARG VITE_FIREBASE_PROJECT_ID
ARG VITE_FIREBASE_STORAGE_BUCKET
ARG VITE_FIREBASE_MESSAGING_SENDER_ID
ARG VITE_FIREBASE_APP_ID
ARG VITE_FIREBASE_VAPID_KEY

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
     && docker-php-ext-install opcache \
     && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for caching (--no-scripts: artisan doesn't exist yet)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy app code, then run deferred post-install scripts (package:discover etc.)
COPY . .
RUN mkdir -p bootstrap/cache && composer run-script post-autoload-dump

# Opcache tuning
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Accept an optional version argument during build, default is 'latest'
ARG APP_VERSION=latest
ENV APP_VERSION=${APP_VERSION}

# Generate a build version & timestamp tracker (cache breaks here on code changes)
RUN echo "App Version: ${APP_VERSION}" > public/build_version.txt \
 && date +"Build Date:  %Y-%m-%d %H:%M:%S" >> public/build_version.txt

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
# RUN env

# Set the server name to localhost
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy entrypoint script from repository (version-controlled)
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
