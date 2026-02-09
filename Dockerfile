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

# Create PHP-FPM pool config with Unix socket
RUN echo '[www]\n\
user = www-data\n\
group = www-data\n\
listen = /var/run/php-fpm.sock\n\
listen.owner = www-data\n\
listen.group = www-data\n\
listen.mode = 0660\n\
pm = dynamic\n\
pm.max_children = 10\n\
pm.start_servers = 2\n\
pm.min_spare_servers = 1\n\
pm.max_spare_servers = 3\n\
catch_workers_output = yes\n\
' > /usr/local/etc/php-fpm.d/www.conf

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
RUN chown -R www-data:www-data /var/www /var/run \
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
echo "Starting PHP-FPM..."\n\
php-fpm -D\n\
\n\
# Wait for socket\n\
echo "Waiting for PHP-FPM socket..."\n\
for i in {1..15}; do\n\
    if [ -S /var/run/php-fpm.sock ]; then\n\
        echo "✓ PHP-FPM socket ready"\n\
        break\n\
    fi\n\
    sleep 1\n\
done\n\
\n\
# Ensure permissions\n\
chown www-data:www-data /var/run/php-fpm.sock\n\
chmod 660 /var/run/php-fpm.sock\n\
\n\
# Test Nginx\n\
nginx -t\n\
\n\
# Start Nginx in background\n\
echo "Starting Nginx on port $PORT..."\n\
nginx\n\
\n\
echo "========================================"\n\
echo "✓ Application is RUNNING on port $PORT"\n\
echo "========================================"\n\
\n\
# Keep container alive and monitor\n\
while true; do\n\
    # Check if nginx is running\n\
    if ! pgrep nginx > /dev/null; then\n\
        echo "ERROR: Nginx died!"\n\
        exit 1\n\
    fi\n\
    # Check if php-fpm is running\n\
    if ! pgrep php-fpm > /dev/null; then\n\
        echo "ERROR: PHP-FPM died!"\n\
        exit 1\n\
    fi\n\
    # Show we are alive\n\
    echo "[$(date +"%Y-%m-%d %H:%M:%S")] Status: Running (Nginx: ✓ PHP-FPM: ✓)"\n\
    sleep 30\n\
done\n\
' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]