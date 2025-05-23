FROM php:8.2-apache

ENV PORT=8080
ENV APACHE_DOCUMENT_ROOT=/var/www/idp-app/public

# PHP extensions
RUN apt-get update && \
    apt-get install -y libicu-dev git unzip && \
    docker-php-ext-install intl && \
    a2enmod rewrite && \
    sed -i 's|DocumentRoot /var/www/html|DocumentRoot ${APACHE_DOCUMENT_ROOT}|' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf && \
    sed -i 's/:80>/:8080>/' /etc/apache2/sites-available/000-default.conf

# Add Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy app
COPY idp-app/ /var/www/idp-app/
WORKDIR /var/www/idp-app

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chown -R www-data:www-data /var/www/idp-app && chmod -R 755 /var/www/idp-app

EXPOSE 8080
