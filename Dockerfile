FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git curl unzip libzip-dev

RUN docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN touch database/database.sqlite

RUN chmod -R 775 storage bootstrap/cache

CMD php artisan serve --host=0.0.0.0 --port=$PORT