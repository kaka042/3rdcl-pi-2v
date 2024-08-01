FROM php:7.4-apache

# Install required PHP extensions and other dependencies
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy the application files
COPY . /var/www/html

# Set permissions for the web server
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/submissions
