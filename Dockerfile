FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql pdo_sqlite gd mbstring zip opcache

# Enable Apache mod_rewrite & allow .htaccess overrides
RUN a2enmod rewrite \
 && echo '<Directory /var/www/html>\n  AllowOverride All\n  Require all granted\n</Directory>' > /etc/apache2/conf-available/override.conf \
 && a2enconf override

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create database and uploads directories and set permissions
RUN mkdir -p /var/www/html/uploads/properties /var/www/html/uploads/agents /var/www/html/uploads/blog /var/www/html/database \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/database /var/www/html/uploads

# Entrypoint script
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN sed -i 's/\r$//' /docker-entrypoint.sh && chmod +x /docker-entrypoint.sh

EXPOSE 80 10000

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
