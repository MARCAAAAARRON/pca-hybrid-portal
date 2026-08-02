#!/bin/bash

# Clear configuration/cache
php artisan optimize:clear

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Publish Filament assets (including fullcalendar)
echo "Publishing Filament assets..."
php artisan filament:assets

# Start Apache in the foreground
echo "Starting Apache..."
apache2-foreground
