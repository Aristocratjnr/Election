FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    default-mysql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip intl xml

# Enable Apache modules
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Set file permissions
RUN chown -R www-data:www-data /var/www/html
RUN find . -type d -exec chmod 755 {} \;
RUN find . -type f -exec chmod 644 {} \;

# Create logs directory and set permissions
RUN mkdir -p configs/logs
RUN chown -R www-data:www-data configs/logs
RUN chmod -R 775 configs/logs

# Ensure uploads directory exists and is writable
RUN mkdir -p uploads
RUN chown -R www-data:www-data uploads
RUN chmod -R 775 uploads

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader

# Configure Apache
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Create .env file from example if it doesn't exist
RUN if [ ! -f .env ]; then cp docker/.env.example .env; fi

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-ctl", "-D", "FOREGROUND"]