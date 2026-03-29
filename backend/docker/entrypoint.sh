#!/bin/sh
set -e

# Fix storage & cache permissions after volume mount (overrides Dockerfile chown)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy .env if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate app key if not set
php artisan key:generate --no-interaction --force

# Run migrations
php artisan migrate --force --no-interaction

# Seed data from JSONPlaceholder
php artisan app:fetch-jsonplaceholder --no-interaction

# Start PHP-FPM
exec php-fpm
