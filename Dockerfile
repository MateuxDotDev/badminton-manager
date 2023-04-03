# Imagem base oficial do PHP 8.1 com Apache
FROM php:8.1-apache

# Atualiza os pacotes e instala as dependências necessárias
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y git zip unzip

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Instala o Xdebug e habilita-o
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configura o Xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Copia os arquivos do projeto para o container
COPY . /var/www/html

# Define a pasta /var/www como a raiz do Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Altera o DocumentRoot do Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Expõe a porta 80 para acesso externo
EXPOSE 80
