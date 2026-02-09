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
    nginx \
    gettext-base

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Configure PHP-FPM to use Unix socket
RUN sed -i 's/listen = 127.0.0.1:9000/listen = \/var\/run\/php-fpm.sock/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;listen.owner = www-data/listen.owner = www-data/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;listen.group = www-data/listen.group = www-data/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;listen.mode = 0660/listen.mode = 0660/' /usr/local/etc/php-fpm.d/www.conf

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
    /var/www/bootstrap/cache \
    /var/run \
    /var/log/nginx

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Configure Nginx with Unix socket
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default

RUN echo 'server {\n\
    listen ${PORT} default_server;\n\
    server_name _;\n\
    root /var/www/public;\n\
    index index.php index.html;\n\
    \n\
    client_max_body_size 100M;\n\
    \n\
    error_log /var/log/nginx/error.log;\n\
    access_log /var/log/nginx/access.log;\n\
    \n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    \n\
    location ~ \\.php$ {\n\
        try_files $uri =404;\n\
        fastcgi_split_path_info ^(.+\\.php)(/.+)$;\n\
        fastcgi_pass unix:/var/run/php-fpm.sock;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
    }\n\
    \n\
    location ~ /\\.(?!well-known).* {\n\
        deny all;\n\
    }\n\
}\n\
' > /etc/nginx/sites-available/default.template

# Create startup script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
export PORT=${PORT:-8080}\n\
\n\
echo "========================================"\n\
echo "Starting Buzz Events on port $PORT"\n\
echo "========================================"\n\
\n\
# Generate Nginx config\n\
envsubst "\$PORT" < /etc/nginx/sites-available/default.template > /etc/nginx/sites-available/default\n\
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/\n\
\n\
# Clear caches\n\
php artisan cache:clear 2>/dev/null || true\n\
php artisan config:clear 2>/dev/null || true\n\
php artisan route:clear 2>/dev/null || true\n\
php artisan view:clear 2>/dev/null || true\n\
\n\
# Run migrations\n\
echo "Running migrations..."\n\
php artisan migrate --force 2>&1 || echo "Migrations skipped"\n\
\n\
# Start PHP-FPM in background\n\
echo "Starting PHP-FPM with Unix socket..."\n\
php-fpm -D\n\
\n\
# Wait for socket to be created\n\
echo "Waiting for PHP-FPM socket..."\n\
for i in {1..10}; do\n\
    if [ -S /var/run/php-fpm.sock ]; then\n\
        echo "✓ PHP-FPM socket created: /var/run/php-fpm.sock"\n\
        ls -la /var/run/php-fpm.sock\n\
        break\n\
    fi\n\
    echo "Waiting for socket... ($i/10)"\n\
    sleep 1\n\
done\n\
\n\
if [ ! -S /var/run/php-fpm.sock ]; then\n\
    echo "✗ ERROR: PHP-FPM socket not created!"\n\
    exit 1\n\
fi\n\
\n\
# Test Nginx config\n\
echo "Testing Nginx configuration..."\n\
nginx -t\n\
\n\
# Start Nginx\n\
echo "Starting Nginx on port $PORT..."\n\
echo "========================================"\n\
echo "Application ready!"\n\
echo "========================================"\n\
exec nginx -g "daemon off;"\n\
' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]