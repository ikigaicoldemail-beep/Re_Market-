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

# Ensure public/storage resolves *inside* the container. A storage link created
# on the Windows host (junction / git-bash symlink) points at a host path like
# /mnt/host/c/... that does not exist here, so it dangles and every /storage/*
# request 404/403s. Force a relative symlink that resolves in both worlds.
if [ ! -d public/storage ]; then
  rm -f public/storage
  ln -s ../storage/app/public public/storage
fi

exec "$@"
