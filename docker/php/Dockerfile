FROM php:8.3-fpm

COPY php.ini /usr/local/etc/php/

RUN apt-get update \
  && apt-get install -y \
      default-mysql-client \
      zlib1g-dev \
      libzip-dev \
      unzip \
      libicu-dev \
      g++ \
  && docker-php-ext-install pdo_mysql zip intl \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
