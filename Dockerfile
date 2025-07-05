# Use official PHP image with Apache and SQLite
FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy website files to Apache root
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Optional: Set working directory
WORKDIR /var/www/html

# Optional: Custom PHP.ini (increase upload limits, etc.)
# COPY php.ini /usr/local/etc/php/

# Expose port 80
EXPOSE 80
