FROM php:8.4-cli

# Instalar dependencias del sistema y del driver de Postgres
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP necesarias para Postgres y Laravel
RUN docker-php-ext-install pdo pdo_pgsql pgsql gd opcache

# OPcache también para `artisan serve` (es CLI): sin esto PHP recompila cada archivo
# en cada petición, lo que en Windows (código compartido con la VM de Docker) es muy lento.
RUN printf 'opcache.enable_cli=1\nrealpath_cache_ttl=600\n' > /usr/local/etc/php/conf.d/opcache-cli.ini

# Composer dentro de la imagen (en Windows, vendor/ se instala en un volumen de Docker)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Subidas: evidencias del formulario (10 MB c/u) e imágenes del CMS (4 MB)
RUN printf 'upload_max_filesize=12M\npost_max_size=64M\n' > /usr/local/etc/php/conf.d/uploads.ini

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar el código fuente del proyecto
COPY . /var/www/html

# Exponer el puerto del servidor de desarrollo de Laravel
EXPOSE 8000

# Ejecutar el servidor de desarrollo de Laravel por defecto
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
