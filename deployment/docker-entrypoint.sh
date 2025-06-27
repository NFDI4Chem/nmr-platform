#!/bin/sh
set -e

# Ensure cache directory exists and is writable
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/bootstrap/cache

exec "$@"