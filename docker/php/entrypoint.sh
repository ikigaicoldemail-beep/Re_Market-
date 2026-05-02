#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
  composer install
fi

mkdir -p storage bootstrap/cache database
chmod -R ug+rwX storage bootstrap/cache database || true

exec "$@"
