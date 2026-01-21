FROM php:8.1-apache

# Enable mysqli
RUN docker-php-ext-install mysqli

# Copy project
COPY . /var/www/html/

# Allow .htaccess
RUN a2enmod rewrite
