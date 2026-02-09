# ... Tout le reste du Dockerfile reste identique jusqu'au script de démarrage ...

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