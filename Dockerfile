FROM php:8.1-apache

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
RUN chown -R www-data:www-data /var/www/html/submissions && chmod -R 777 /var/www/html/submissions

# Keep-alive script to prevent the Render instance from spinning down
COPY keep_alive.py /keep_alive.py
RUN chmod +x /keep_alive.py

# Start the keep-alive script in the background and then start the Apache server
CMD ["sh", "-c", "nohup python3 /keep_alive.py & apache2-foreground"]
