FROM php:8.4-apache-bookworm AS php-base
RUN apt-get update && apt-get install -y --no-install-recommends \
      curl unzip libicu-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j2 intl gd pdo_mysql bcmath zip exif pcntl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html

FROM php-base AS dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY . ./
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

FROM node:22-bookworm-slim AS frontend
WORKDIR /build
COPY --from=dependencies /var/www/html /build
RUN corepack enable && yarn install --frozen-lockfile && yarn build

FROM php-base AS runtime
ARG SOURCE_REVISION=unknown
LABEL org.opencontainers.image.source="https://github.com/TheFreeman/InvoicePlane-v2" \
      org.opencontainers.image.revision="$SOURCE_REVISION"
COPY --from=dependencies --chown=www-data:www-data /var/www/html /var/www/html
COPY --from=frontend --chown=www-data:www-data /build/public/build /var/www/html/public/build
COPY docker/production/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/production/php.ini /usr/local/etc/php/conf.d/invoiceplane.ini
COPY docker/production/entrypoint.sh /usr/local/bin/invoiceplane-entrypoint
RUN chmod 755 /usr/local/bin/invoiceplane-entrypoint \
    && mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && ln -s /var/www/html/storage/app/public public/storage
ENTRYPOINT ["invoiceplane-entrypoint"]
CMD ["apache2-foreground"]
