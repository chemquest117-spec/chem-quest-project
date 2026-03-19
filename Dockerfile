# Use PHP with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
     git \
     curl \
     zip \
     unzip \
     libpq-dev \
     libzip-dev \
     && docker-php-ext-install pdo pdo_pgsql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Install Node
RUN apt-get install -y nodejs npm

# Build frontend
RUN npm install
RUN npm run build

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
     && chmod -R 755 /var/www/html/storage

# Change Apache root to /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]