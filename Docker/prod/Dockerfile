# Imagem base do PHP 8.1 com FPM Alpine
FROM php:8.1-fpm-alpine

# Instalar as dependências necessárias
RUN apk update && apk upgrade \
    && apk add --no-cache nginx git zip unzip postgresql-dev libpq \
    && docker-php-ext-install pdo_pgsql \
    && mkdir -p /run/nginx \
    && chown -R www-data:www-data /run/nginx

# Copia os arquivos do projeto para o container
COPY ../.. /var/www/html

# Move a pasta src para public
RUN mv /var/www/html/src /var/www/html/public

# Define a pasta /var/www/html como a raiz do Nginx
ENV NGINX_DOCUMENT_ROOT /var/www/html/public

# Instala o Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala as dependências do projeto
RUN composer install --no-dev --optimize-autoloader

# Configura o Nginx
COPY Docker/prod/nginx.conf /etc/nginx/nginx.conf

# Define a pasta /var/www/html como a raiz do PHP-FPM
ENV PHP_FPM_DOCUMENT_ROOT /var/www/html/public

# Altera as permissões do diretório /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta 80 para acesso externo
EXPOSE 80

# Inicia o Nginx e o PHP-FPM
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
