#!/bin/bash

# Clear configuration/cache
php artisan optimize:clear

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Publish Filament assets (including fullcalendar)
echo "Publishing Filament assets..."
php artisan filament:assets

# Start Supervisor to run Apache and Laravel Queue Worker concurrently
echo "Starting Supervisor (Apache + Laravel Queue Worker)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
