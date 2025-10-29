FROM php:8.2-fpm

# основные зависимости
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev zip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# права (опционально)
RUN useradd -G www-data,root -u 1000 -d /home/appuser appuser
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN chmod -R 755 storage bootstrap/cache \
    && chown -R appuser:www-data storage bootstrap/cache
RUN php artisan migrate --force || true

EXPOSE 9000
CMD ["php-fpm", "-F", "-R"]

    
