FROM php:8.4-cli

# Instalar dependencias del sistema y del driver de Postgres
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP necesarias para Postgres y Laravel
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar el código fuente del proyecto
COPY . /var/www/html

# Exponer el puerto del servidor de desarrollo de Laravel
EXPOSE 8000

# Ejecutar el servidor de desarrollo de Laravel por defecto
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
