#!/bin/sh
set -e

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
