#!/bin/sh
set -eu
cd /var/www/html
mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache
exec docker-php-entrypoint "$@"
