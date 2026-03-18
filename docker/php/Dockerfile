FROM php:8.4-fpm

# Sistem bağımlılıkları
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libredis-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP eklentileri
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Redis eklentisi
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Çalışma dizini
WORKDIR /var/www/html

# PHP konfigürasyonu
COPY docker/php/php.ini /usr/local/etc/php/conf.d/kirpi.ini

EXPOSE 9000

CMD ["php-fpm"]