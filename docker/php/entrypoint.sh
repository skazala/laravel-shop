#!/bin/sh
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Installing Horizon..."
php artisan horizon:install

echo "Starting service..."
exec "$@"