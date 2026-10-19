FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libldap2-dev libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
        libicu-dev libcurl4-openssl-dev libssl-dev libidn2-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql ldap gd bcmath zip intl pcntl \
    && pecl install raphf && docker-php-ext-enable raphf \
    && pecl install pecl_http && docker-php-ext-enable http \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
