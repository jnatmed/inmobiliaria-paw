# Usar una imagen base oficial de PHP con Apache
FROM php:7.4-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Habilitar módulos de Apache
RUN a2enmod rewrite

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Copiar el archivo composer.json y composer.lock
COPY composer.json ./
COPY composer.lock ./

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar dependencias de Composer
RUN composer install

# Copiar el resto de la aplicación
COPY . ./

# Exponer el puerto 80 para el servidor web
EXPOSE 80

# Comando para iniciar Apache en el contenedor
CMD ["apache2-foreground"]
