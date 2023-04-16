# Imagem base do PHP 8.1 com FPM Alpine
FROM php:8.1-fpm-alpine

# Criar um grupo e usuário nonroot
RUN addgroup -S nonroot \
    && adduser -S nonroot -G nonroot

# Instalar as dependências necessárias
RUN apk update && apk upgrade \
    && apk add --no-cache nginx git zip unzip postgresql-dev libpq \
    && docker-php-ext-install pdo_pgsql

# Configurar os diretórios e permissões do Nginx
RUN mkdir -p /run/nginx \
    && mkdir -p /var/lib/nginx/logs \
    && mkdir -p /var/lib/nginx/tmp \
    && chown -R nonroot:nonroot /run/nginx \
    && chown -R nonroot:nonroot /var/lib/nginx

# Copia os arquivos do projeto para o container
COPY --chown=nonroot:nonroot ../../composer.json /var/www/html/
COPY --chown=nonroot:nonroot ../../composer.lock /var/www/html/
COPY --chown=nonroot:nonroot ../../src /var/www/html/src
COPY --chown=nonroot:nonroot .env /var/www/html/

# Define a pasta /var/www/html como a raiz do Nginx
ENV NGINX_DOCUMENT_ROOT /var/www/html/src

# Instala o Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala as dependências do projeto
RUN composer install --no-dev --optimize-autoloader

# Configura o Nginx
COPY --chown=nonroot:nonroot Docker/prod/nginx.conf /etc/nginx/nginx.conf

# Define a pasta /var/www/html como a raiz do PHP-FPM
ENV PHP_FPM_DOCUMENT_ROOT /var/www/html/src

# Altera as permissões do diretório /var/www/html
RUN chown -R nonroot:nonroot /var/www/html

# Expõe a porta 80 para acesso externo
EXPOSE 80

# Muda para o usuário nonroot
USER nonroot

# Inicia o Nginx e o PHP-FPM
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
