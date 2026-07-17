FROM php:8.4-cli-alpine

# Match the host user so files created in mounted volumes (vendor/,
# storage/, compiled views) keep sane ownership. Override at build time:
#   docker compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g) cli
ARG UID=1000
ARG GID=1000

RUN addgroup -g ${GID} dockeruser \
    && adduser -D -s /bin/bash -u ${UID} -G dockeruser dockeruser

# Install build dependencies (temporary)
RUN apk add --no-cache --virtual .build-deps \
        autoconf \
        g++ \
        make \
        pkgconf \
        zstd-dev \
    # Install runtime dependencies (permanent)
    && apk add --no-cache \
        bash \
        git \
        curl \
        zip \
        unzip \
        icu-dev \
        libxml2-dev \
        oniguruma-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        zstd \
    # Configure and install PHP extensions (pdo_sqlite ships with the base
    # image — the test suite runs on an in-memory sqlite database)
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        xml \
        soap \
        opcache \
    # Install PECL extensions
    && pecl install redis \
    && docker-php-ext-enable redis \
    # Remove only build dependencies
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

# PHPUnit needs more than the 128M default on the full suite
RUN echo 'memory_limit=1G' > /usr/local/etc/php/conf.d/memory-limit.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER dockeruser

WORKDIR /var/www/html

CMD ["php", "-a"]
