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
    gettext-base \
    procps

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Configure PHP-FPM to listen on 127.0.0.1:9000
RUN echo "[www]" > /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "listen = 127.0.0.1:9000" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "listen.owner = www-data" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "listen.group = www-data" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "pm = dynamic" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "pm.max_children = 10" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "pm.start_servers = 2" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "pm.min_spare_servers = 1" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "pm.max_spare_servers = 3" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "catch_workers_output = yes" >> /usr/local/etc/php-fpm.d/zz-docker.conf && \
    echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/zz-docker.conf

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
    /var/log/nginx

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Configure Nginx
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default

# Create Nginx config TEMPLATE
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
# Start PHP-FPM\n\
echo "Starting PHP-FPM on 127.0.0.1:9000..."\n\
php-fpm -F &\n\
PHP_FPM_PID=$!\n\
\n\
# Wait for PHP-FPM\n\
sleep 3\n\
\n\
# Check if PHP-FPM is listening\n\
if netstat -tuln 2>/dev/null | grep -q ":9000"; then\n\
    echo "✓ PHP-FPM is listening on port 9000"\n\
else\n\
    echo "✗ WARNING: PHP-FPM might not be listening on port 9000"\n\
fi\n\
\n\
# Test Nginx config\n\
nginx -t\n\
\n\
# Start Nginx\n\
echo "Starting Nginx on port $PORT..."\n\
echo "========================================"\n\
exec nginx -g "daemon off;"\n\
' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]