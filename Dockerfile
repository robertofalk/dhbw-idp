FROM php:8.2-apache

# Install intl extension for CodeIgniter
RUN apt-get update && \
    apt-get install -y libicu-dev && \
    docker-php-ext-install intl

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy entire project to /var/www/html/idp-app
COPY idp-app/ /var/www/idp-app/

# Change DocumentRoot to /var/www/idp-app/public
ENV APACHE_DOCUMENT_ROOT=/var/www/idp-app/public

# Update Apache config to respect that
RUN sed -i "s|DocumentRoot /var/www/html|DocumentRoot ${APACHE_DOCUMENT_ROOT}|" /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/idp-app

# Set permissions
RUN chown -R www-data:www-data /var/www/idp-app \
    && chmod -R 755 /var/www/idp-app

EXPOSE 80
