#!/bin/sh
set -e

# Ensure bootstrap/cache exists
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/bootstrap/cache

# Ensure storage/framework/cache/data exists
mkdir -p /var/www/html/storage/framework/cache/data
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

exec "$@"
