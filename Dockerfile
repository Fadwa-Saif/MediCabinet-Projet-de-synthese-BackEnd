FROM php:8.2-cli

WORKDIR /app

# install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libzip-dev

# install php extensions required by Laravel
RUN docker-php-ext-install pdo pdo_mysql zip

# install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# copy project files into container
COPY . .

# install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# fix permissions (Laravel needs this)
RUN chmod -R 775 storage bootstrap/cache

# expose port Render will use
EXPOSE 10000

# start Laravel server
CMD php artisan serve --host=0.0.0.0 --port=10000