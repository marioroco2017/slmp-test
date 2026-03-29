#!/bin/sh
set -e

# Install dependencies if vendor missing
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy .env
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Laravel setup
php artisan key:generate --no-interaction --force
php artisan migrate --force --no-interaction
php artisan app:fetch-jsonplaceholder --no-interaction

exec php-fpm