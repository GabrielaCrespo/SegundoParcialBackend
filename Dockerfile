# ------------------------------------------------------
# PHP + Laravel + Composer + Extensions necesarias
# ------------------------------------------------------
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev

# Extensiones de PHP necesarias para Laravel
RUN docker-php-ext-install pdo pdo_pgsql zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Generar storage link
RUN php artisan storage:link || true

# Permisos (IMPORTANTE para Render)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Puerto que usará Laravel
EXPOSE 8080

# Comando para iniciar Laravel
CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port 8080
