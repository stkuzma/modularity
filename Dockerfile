
# ---------------------------------------------------------------------------
# Base images are pinned to exact versions. A floating tag makes the image you
# ship depend on the day you built it, which is the opposite of what a
# reproducible rollout needs.
# ---------------------------------------------------------------------------

# ---------------------------------------------------------------------------
# Assets. Tailwind scans module views, so app/ has to be present to build.
# ---------------------------------------------------------------------------
FROM node:22.20-alpine AS assets

WORKDIR /build

COPY package.json package-lock.json vite.config.js ./
RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY app ./app
RUN npm run build

# ---------------------------------------------------------------------------
# Dependencies. composer.json pins config.platform.php, so this resolves for
# the runtime's PHP version rather than for whatever this image happens to run.
# ---------------------------------------------------------------------------
FROM composer:2.8 AS vendor

WORKDIR /build

# Dependencies first, so editing application code does not re-resolve them.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .

# Run again with the code present. This is not redundant: it makes the stage
# correct by construction rather than by trusting that .dockerignore kept a
# developer's dev-dependency vendor tree out of the context. Anything the lock
# does not require without dev is removed here.
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction \
    --optimize-autoloader --classmap-authoritative

# ---------------------------------------------------------------------------
# Runtime.
# ---------------------------------------------------------------------------
FROM php:8.5.10-fpm AS runtime

# Only pdo_pgsql. OPcache is already compiled into the official image, and
# asking docker-php-ext-install for it again reconfigures the source tree and
# wipes the modules directory before pdo_pgsql is installed. It is configured
# in modularity.ini, not installed here.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && apt-get purge -y --auto-remove libpq-dev \
    && apt-get install -y --no-install-recommends libpq5 \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/modularity.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

WORKDIR /var/www/html

COPY --from=vendor /build /var/www/html
COPY --from=assets /build/public/build /var/www/html/public/build

# The framework needs to write here and nowhere else, plus the directory the
# assets are published into. Creating and chowning it in the image matters:
# Docker seeds a new named volume from the image path, ownership included, and
# a root-owned volume would leave this container unable to write to it.
RUN mkdir -p /srv/public \
    && chown -R www-data:www-data storage bootstrap/cache /srv/public

USER www-data

# The rollout gates on this rather than on an HTTP probe: at that moment the
# container is not behind the proxy yet.
HEALTHCHECK --interval=10s --timeout=5s --start-period=20s --retries=3 \
    CMD php artisan health:check --quiet-on-success || exit 1

EXPOSE 9000

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
