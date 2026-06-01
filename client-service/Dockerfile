# syntax=docker/dockerfile:1
# =============================================================================
# DriveNow ERP — auth-service container (multi-stage)
#   Stage 1 (build):  PHP 8.3 CLI + Composer + Node 22 — installs vendor and
#                     builds the Vite/Inertia assets. PHP is required in this
#                     stage because the Wayfinder Vite plugin shells out to
#                     `php artisan wayfinder:generate` during `vite build`.
#   Stage 2 (runtime): php-fpm + nginx + supervisord serving public/.
# =============================================================================

# ----------------------------------------------------------------------------
# Stage 1 — build (vendor + compiled front-end assets)
# ----------------------------------------------------------------------------
FROM php:8.4-cli-bookworm AS build

# System deps + PHP extensions needed to bootstrap artisan during the build.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libzip-dev ca-certificates curl gnupg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql zip \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# Composer binary from the official image.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP dependencies first (better layer caching).
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-interaction --no-progress --prefer-dist

# Bring in the rest of the source and finish the composer install (runs
# package:discover + dumps the optimized autoloader against the full tree).
COPY . .
RUN composer install --optimize-autoloader --no-interaction --no-progress --prefer-dist

# Minimal throwaway .env — only enough for `php artisan` to boot during the
# Vite/Wayfinder build step. APP_URL is intentionally left EMPTY so that
# laravel-vite-plugin generates root-relative asset paths (/build/assets/...)
# instead of absolute URLs tied to a hardcoded host. The real APP_URL is
# injected at runtime via docker-compose env vars.
RUN printf 'APP_NAME=DriveNow\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=\nDB_CONNECTION=sqlite\nSESSION_DRIVER=array\nCACHE_STORE=array\nQUEUE_CONNECTION=sync\n' > .env \
    && php artisan key:generate --force

# Build the front-end (Vite -> public/build). Wayfinder regenerates the typed
# route/action files here via `php artisan` (PHP + vendor are present).
RUN npm install --no-audit --no-fund && npm run build

# Drop the throwaway env and build-only front-end deps from the tree.
RUN rm -f .env && rm -rf node_modules

# ----------------------------------------------------------------------------
# Stage 2 — runtime (php-fpm + nginx + supervisord)
# ----------------------------------------------------------------------------
FROM php:8.4-fpm-bookworm AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx supervisor curl libpq-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql zip opcache \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default

WORKDIR /var/www/html

# Tuned PHP + nginx + process-manager config.
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-drivenow.ini
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# App (vendor + built assets) from the build stage.
COPY --from=build --chown=www-data:www-data /app /var/www/html

RUN mkdir -p storage/framework/sessions storage/framework/views \
        storage/framework/cache storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

# Health endpoint is Laravel's built-in /up route.
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=5 \
    CMD curl -fsS http://127.0.0.1/up || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
