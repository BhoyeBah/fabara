FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libonig-dev libsqlite3-dev libsqlite3-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" pdo_sqlite zip gd intl bcmath mbstring opcache \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
 && printf 'upload_max_filesize=64M\npost_max_size=64M\nmemory_limit=512M\n' > /usr/local/etc/php/conf.d/fabara.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
 && php artisan filament:assets || true
RUN chmod +x docker/start.sh

EXPOSE 8080
CMD ["docker/start.sh"]
