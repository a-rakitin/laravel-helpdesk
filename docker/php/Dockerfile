FROM php:8.4-fpm

# System deps + PHP extensions for Laravel/Postgres
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        intl \
        zip \
    && rm -rf /var/lib/apt/lists/*

# Redis extension (for cache/queue via Redis)
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer (copy from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
