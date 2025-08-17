# Usando base estável para Mac M1/M2
FROM php:8.2-fpm-bullseye

# Instala dependências necessárias para Laravel
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip zip curl libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring xml zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

EXPOSE 9000

CMD ["php-fpm"]
