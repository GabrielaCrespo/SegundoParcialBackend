# ------------------------------------------------------
# Imagen base PHP 8.2 con soporte FPM
# ------------------------------------------------------
FROM php:8.2-fpm

# ------------------------------------------------------
# Instalar dependencias necesarias del sistema
# ------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    libssl-dev \
    libonig-dev \
    supervisor

# ------------------------------------------------------
# Instalar extensiones de PHP necesarias para tu proyecto
# ------------------------------------------------------
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip

# ------------------------------------------------------
# Instalar Composer
# ------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ------------------------------------------------------
# Configurar carpeta de trabajo
# ------------------------------------------------------
WORKDIR /var/www/html

# Copiar proyecto
COPY . .

# ------------------------------------------------------
# Instalar dependencias PHP del proyecto
# ------------------------------------------------------
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# ------------------------------------------------------
# Generar storage link (si falla no rompe el build)
# ------------------------------------------------------
RUN php artisan storage:link || true

# ------------------------------------------------------
# Permisos de Laravel
# ------------------------------------------------------
RUN chown -R www-data:www-data storage bootstrap/cache

# Exponer el puerto estándar para Laravel en Render
EXPOSE 8080

# ------------------------------------------------------
# Comando de ejecución en Render
# ------------------------------------------------------
CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port 8080
