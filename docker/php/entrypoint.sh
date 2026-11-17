#!/bin/sh
set -e
cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
  echo "[kotakia] composer install..."
  composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
  cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64' .env; then
  php artisan key:generate --force
fi

mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  echo "[kotakia] menunggu MySQL..."
  i=0
  until php artisan db:show >/dev/null 2>&1 || [ $i -ge 30 ]; do i=$((i+1)); sleep 2; done
  php artisan migrate --force
  if [ "${RUN_SEEDERS:-true}" = "true" ]; then
    php artisan db:seed --force || true
  fi
fi

exec "$@"
