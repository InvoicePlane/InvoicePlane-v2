FROM php:8.4-fpm-alpine

RUN adduser -D -s /bin/bash dockeruser

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
    # Configure and install PHP extensions
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

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER dockeruser

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
