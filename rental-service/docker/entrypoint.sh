#!/bin/sh
# DriveNow ERP — container entrypoint: prepare storage, wait for DB, migrate,
# (optionally) seed, warm caches, then hand off to the CMD (supervisord).
set -e

cd /var/www/html

# Ensure writable runtime dirs (the bind/volume mount may shadow image perms).
mkdir -p storage/framework/sessions storage/framework/views \
         storage/framework/cache storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# Public symlink so uploaded files (logo/avatars/vehicle photos) are servable.
php artisan storage:link >/dev/null 2>&1 || true

# Wait for the database, then run migrations (idempotent — only pending run).
echo "[entrypoint] running migrations..."
tries=0
until php artisan migrate --force; do
    tries=$((tries + 1))
    if [ "$tries" -ge 30 ]; then
        echo "[entrypoint] database not reachable after 30 attempts — aborting." >&2
        exit 1
    fi
    echo "[entrypoint] database not ready, retry $tries/30 in 3s..."
    sleep 3
done

# First-boot demo data: set APP_AUTO_SEED=true on the very first deploy only.
if [ "${APP_AUTO_SEED}" = "true" ]; then
    echo "[entrypoint] seeding database..."
    php artisan db:seed --force || true
fi

# Warm config/route/view caches for production. Done at boot (after env is
# present) rather than baked into the image so env vars resolve correctly.
php artisan config:cache >/dev/null 2>&1 || true
php artisan route:cache  >/dev/null 2>&1 || true
php artisan view:cache   >/dev/null 2>&1 || true

echo "[entrypoint] ready — starting web server."
# Render and similar platforms assign the HTTP port via $PORT; make nginx use it.
if [ -n "$PORT" ]; then
    sed -i "s/listen 80 default_server;/listen ${PORT} default_server;/" /etc/nginx/conf.d/default.conf || true
fi

exec "$@"
