FROM php:8.1-fpm

WORKDIR /var/www

COPY ./php.ini /usr/local/etc/php/

RUN apt-get update && apt-get install -y \
    curl \
    wget \
    git \
    unzip \
    libfreetype6-dev \
    libicu-dev \
    libonig-dev \
    libzip-dev

# PHP拡張のインストール
RUN docker-php-ext-install \
    intl \
    mbstring \
    zip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Composerのインストール
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
