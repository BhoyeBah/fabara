#!/usr/bin/env bash
set -e
cd /var/www/html

# Le volume Railway (monté sur /data) garde la base et les photos entre deux mises à jour.
mkdir -p /data/storage-public
touch /data/database.sqlite
rm -rf storage/app/public && ln -s /data/storage-public storage/app/public
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data /data storage bootstrap/cache

export DB_CONNECTION=sqlite
export DB_DATABASE=/data/database.sqlite
export APP_ENV=${APP_ENV:-production}
export APP_DEBUG=${APP_DEBUG:-false}
export SESSION_DRIVER=${SESSION_DRIVER:-database}
export CACHE_STORE=${CACHE_STORE:-database}
export QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}

php artisan migrate --force
# Première fois seulement : sous-menus + compte administrateur (ADMIN_EMAIL / ADMIN_PASSWORD)
if [ "$(php artisan tinker --execute='echo \App\Models\User::count();' 2>/dev/null | tail -n1)" = "0" ]; then
  php artisan db:seed --force
fi
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
chown -R www-data:www-data /data storage bootstrap/cache

# Apache doit écouter sur le port donné par l'hébergeur
PORT=${PORT:-8080}
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
exec apache2-foreground
