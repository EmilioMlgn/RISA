FROM php:8.4-fpm-alpine

# Instalar dependencias del sistema y librerías para extensiones de PHP
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    linux-headers \
    shadow

# Instalar extensiones de PHP necesarias para Laravel y PostgreSQL
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    bcmath \
    ctype \
    fileinfo \
    mbstring \
    pcntl \
    xml \
    zip \
    opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www

# Copiar configuración personalizada de PHP
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Asegurar permisos adecuados para el usuario www-data
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Exponer puerto 9000 para PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
