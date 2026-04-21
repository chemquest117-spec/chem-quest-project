#!/bin/bash
set -e

echo "======================================="
echo "      Starting ChemTrack Container "
echo "======================================="

cat /var/www/html/public/build_version.txt || true

echo
echo "======================================="

if [ "${CLEAR_CACHES_ON_BOOT:-true}" = "true" ]; then
  php artisan config:clear || true
  php artisan route:clear || true
  php artisan view:clear || true
  php artisan cache:clear || true
  php artisan optimize:clear || true
  composer clear-cache || true
fi

# Build caches in production-like environments (keeps runtime fast).
if [ "${OPTIMIZE_ON_BOOT:-false}" = "true" ] && [ "${APP_ENV:-production}" != "local" ]; then
  if [ -n "${APP_KEY:-}" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    php artisan event:cache || true
  fi
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
  php artisan db:seed --class=SuperAdminSeeder --force
fi

exec apache2-foreground
