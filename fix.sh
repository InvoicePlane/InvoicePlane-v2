#!/bin/bash

echo "755 on the $APP_DIR directory"
find /var/www/projects/ivplv2 -type d -exec chmod 755 {} \;

echo "755 on the $APP_DIR/storage directory"
find /var/www/projects/ivplv2/storage -type d -exec chmod 775 {} \;

echo "755 on the $APP_DIR/cache directory"
find /var/www/projects/ivplv2/bootstrap/cache -type d -exec chmod 775 {} \;
