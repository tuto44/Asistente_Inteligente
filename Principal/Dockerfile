FROM php:8.2-apache

# Habilitar mod_rewrite para URLs amigables
RUN a2enmod rewrite

# Instalar extensiones PDO para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar el DocumentRoot de Apache a la carpeta /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf

# Habilitar AllowOverride All para que .htaccess funcione
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html