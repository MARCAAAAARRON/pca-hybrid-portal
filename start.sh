#!/bin/bash

# Clear configuration/cache
php artisan optimize:clear

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Start Apache in the foreground
echo "Starting Apache..."
apache2-foreground
