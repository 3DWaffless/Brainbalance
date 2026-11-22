#!/usr/bin/env bash
set -e

PORT_TO_USE="${PORT:-10000}"
sed -i "s/80/${PORT_TO_USE}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
mkdir -p "$(dirname "$DB_PATH")"
if [ ! -f "$DB_PATH" ]; then
    touch "$DB_PATH"
fi
chown -R www-data:www-data "$(dirname "$DB_PATH")" /var/www/html/storage /var/www/html/bootstrap/cache

php artisan storage:link --force || true
php artisan migrate --force
php artisan config:cache
php artisan view:cache

exec apache2-foreground