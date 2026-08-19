FROM php:8.2-apache

# Habilitar mod_rewrite para URLs amigables
RUN a2enmod rewrite

# Instalar extensiones PDO para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html