#!/bin/bash
set -e

echo "======================================="
echo "      Starting ChemTrack Container "
echo "======================================="

cat /var/www/html/public/build_version.txt || true

echo
echo "======================================="

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
php artisan cache:clear

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
fi

exec apache2-foreground
