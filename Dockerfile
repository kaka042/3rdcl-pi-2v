FROM php:7.4-apache

# Install required PHP extensions and other dependencies
RUN apt-get update && \
    apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd && \
    docker-php-ext-install curl && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy the application files
COPY . /var/www/html

# Install dependencies
RUN composer install

# Set permissions for the web server
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/submissions

# Expose port 80
EXPOSE 80

# Start the Apache server
CMD ["apache2-foreground"]
