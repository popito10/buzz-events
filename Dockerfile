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

# Configure Nginx - Template avec variable PORT
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
    error_log /var/log/nginx/error.log debug;\n\
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
        fastcgi_param PATH_INFO $fastcgi_path_info;\n\
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;\n\
        fastcgi_param QUERY_STRING $query_string;\n\
        fastcgi_param REQUEST_METHOD $request_method;\n\
        fastcgi_param CONTENT_TYPE $content_type;\n\
        fastcgi_param CONTENT_LENGTH $content_length;\n\
        fastcgi_intercept_errors on;\n\
        fastcgi_ignore_client_abort off;\n\
        fastcgi_connect_timeout 60;\n\
        fastcgi_send_timeout 180;\n\
        fastcgi_read_timeout 180;\n\
        fastcgi_buffer_size 128k;\n\
        fastcgi_buffers 4 256k;\n\
        fastcgi_busy_buffers_size 256k;\n\
        fastcgi_temp_file_write_size 256k;\n\
        include fastcgi_params;\n\
    }\n\
    \n\
    location ~ /\\.(?!well-known).* {\n\
        deny all;\n\
    }\n\
}\n\
' > /etc/nginx/sites-available/default.template

# Create startup script avec BEAUCOUP plus de logs
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Set default PORT if not provided\n\
export PORT=${PORT:-8080}\n\
\n\
echo "========================================"\n\
echo "Starting Buzz Events on port $PORT"\n\
echo "========================================"\n\
\n\
# Generate Nginx config with actual PORT\n\
echo "Generating Nginx configuration..."\n\
envsubst "\$PORT" < /etc/nginx/sites-available/default.template > /etc/nginx/sites-available/default\n\
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/\n\
\n\
echo "Nginx config generated:"\n\
cat /etc/nginx/sites-available/default\n\
echo "========================================"\n\
\n\
# Clear caches\n\
echo "Clearing Laravel caches..."\n\
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
# Wait and verify PHP-FPM\n\
echo "Waiting for PHP-FPM..."\n\
sleep 3\n\
\n\
if pgrep php-fpm > /dev/null; then\n\
    echo "✓ PHP-FPM is running (PID: $(pgrep php-fpm | head -1))"\n\
else\n\
    echo "✗ ERROR: PHP-FPM failed to start!"\n\
    exit 1\n\
fi\n\
\n\
# Test Nginx config\n\
echo "Testing Nginx configuration..."\n\
nginx -t 2>&1\n\
\n\
# Start Nginx with error output\n\
echo "========================================"\n\
echo "Starting Nginx on port $PORT..."\n\
echo "Application will be available shortly"\n\
echo "========================================"\n\
\n\
# Start nginx and immediately tail logs to keep container alive\n\
nginx -g "daemon off;" 2>&1 &\n\
NGINX_PID=$!\n\
\n\
echo "Nginx started with PID: $NGINX_PID"\n\
echo "Monitoring logs..."\n\
echo "========================================"\n\
\n\
# Keep container alive and show logs\n\
tail -f /var/log/nginx/error.log /var/log/nginx/access.log &\n\
\n\
# Wait for nginx process\n\
wait $NGINX_PID\n\
' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]