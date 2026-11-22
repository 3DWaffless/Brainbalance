# Stage 1: Build Vite / Tailwind CSS assets
FROM node:20-slim AS frontend
WORKDIR /app
COPY . .
RUN npm install && npm run build

# Stage 2: PHP 8.4 + Apache + SQLite runtime
FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    libpng-dev \
    && docker-php-ext-install zip gd bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY start.sh /usr/local/bin/start.sh
RUN sed -i 's/\r$//' /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

EXPOSE 10000
CMD ["/usr/local/bin/start.sh"]