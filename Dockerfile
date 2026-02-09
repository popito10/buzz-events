FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application
COPY . /var/www

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Create storage and cache directories
RUN mkdir -p /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/framework/cache \
    /var/www/bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Configure Nginx - Version corrigée
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default

# Create Nginx config file
RUN echo 'server {\n\
    listen 8080;\n\
    server_name _;\n\
    root /var/www/public;\n\
    index index.php index.html;\n\
    \n\
    error_log /var/log/nginx/error.log;\n\
    access_log /var/log/nginx/access.log;\n\
    \n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    \n\
    location ~ \\.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
    }\n\
    \n\
    location ~ /\\.(?!well-known).* {\n\
        deny all;\n\
    }\n\
}\n\
' > /etc/nginx/sites-available/default

RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Create startup script avec plus de logs
RUN echo '#!/bin/bash\n\
set -e\n\
echo "=== Starting Buzz Events ==="\n\
echo "Clearing caches..."\n\
php artisan cache:clear || true\n\
php artisan config:clear || true\n\
php artisan route:clear || true\n\
php artisan view:clear || true\n\
echo "Running migrations..."\n\
php artisan migrate --force || echo "Migrations failed"\n\
echo "Starting PHP-FPM..."\n\
php-fpm -D\n\
echo "Waiting for PHP-FPM..."\n\
sleep 3\n\
echo "Testing Nginx configuration..."\n\
nginx -t\n\
echo "Starting Nginx..."\n\
exec nginx -g "daemon off;"\n\
' > /start.sh && chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]