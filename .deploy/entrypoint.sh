#!/bin/sh

# Exit on fail
set -e

# Cache configuration
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Link storage
echo "Linking storage..."
php artisan storage:link

# Start Nginx
echo "Starting Nginx..."
service nginx start

# Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm
