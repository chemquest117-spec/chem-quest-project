# -----------------------------
# Stage 1: Build frontend
# -----------------------------
FROM node:18 AS frontend

WORKDIR /app
COPY package*.json ./
RUN npm install

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

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Cache Laravel config, routes, and views
# RUN php artisan config:cache && \
#      php artisan route:cache && \
#      php artisan view:cache

# Copy built frontend assets
COPY --from=frontend /app/public/build ./public/build

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
     && chmod -R 755 /var/www/html/storage

# Set Apache public root
RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
     /etc/apache2/sites-available/000-default.conf

# Create entrypoint script that runs migrations at startup (not build time)
RUN echo '#!/bin/bash\nphp artisan migrate --force\napache2-foreground' > /entrypoint.sh \
     && chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]