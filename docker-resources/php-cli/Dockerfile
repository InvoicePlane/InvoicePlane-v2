FROM php:8.4-cli

# Debian base, matching the image proven to run this suite reliably —
# the equivalent Alpine (musl) build was found to silently drop form
# fields during Livewire component testing (a real, reproducible bug,
# not a database or CI issue). Don't switch back to -alpine without
# re-verifying UserProfileTest::it_saves_the_user_data_form first.

# Match the host user so files created in mounted volumes (vendor/,
# storage/, compiled views) keep sane ownership. Override at build time:
#   docker compose build --build-arg UID=$(id -u) --build-arg GID=$(id -g) cli
ARG UID=1000
ARG GID=1000

RUN groupadd -g ${GID} dockeruser \
    && useradd -m -s /bin/bash -u ${UID} -g dockeruser dockeruser

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        zip \
        unzip \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
    # Configure and install PHP extensions — only the ones NOT already
    # compiled into the base php:8.4-cli image (which already ships
    # mbstring, xml, dom, sodium, opcache, pdo, pdo_sqlite, etc.).
    # Re-installing an already-built-in extension via docker-php-ext-install
    # was tried and produced a real, reproducible bug: Livewire form tests
    # silently lost submitted field values (e.g.
    # UserProfileTest::it_saves_the_user_data_form, ContactsTest — required
    # fields reported as missing even though fillForm() supplied them).
    # Root cause not fully isolated, but the fix is confirmed: stick to this
    # minimal set, matching the proven-reliable ip2-test-php:8.4 image.
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        intl \
        gd \
        pdo_mysql \
        bcmath \
        zip \
        exif \
    && rm -rf /var/lib/apt/lists/*

# PHPUnit needs more than the 128M default on the full suite
RUN echo 'memory_limit=1G' > /usr/local/etc/php/conf.d/memory-limit.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER dockeruser

WORKDIR /var/www/html

CMD ["php", "-a"]
