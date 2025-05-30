FROM php:8.2-fpm

ARG uid=1000
ARG gid=1000

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip sockets \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./

RUN composer install --no-scripts --optimize-autoloader --no-interaction

COPY . .

RUN php artisan package:discover --ansi

RUN chown -R ${uid}:${gid} /var/www/storage /var/www/bootstrap/cache /var/www/.env

RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && chmod 664 /var/www/.env

EXPOSE 9000

CMD ["php-fpm"]