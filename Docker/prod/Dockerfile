# Imagem base do PHP 8.2 com FPM Alpine
FROM php:8.2-fpm-alpine

# Criar um grupo e usuário nonroot
RUN addgroup -S nonroot \
    && adduser -S nonroot -G nonroot

# Instalar as dependências necessárias
RUN apk update && apk upgrade \
    && apk add --no-cache \
        nginx \
        git \
        zip \
        unzip \
        postgresql-dev \
        libpq \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/cache/apk/*

# Configurar os diretórios e permissões do Nginx
RUN mkdir -p /run/nginx \
    && mkdir -p /var/lib/nginx/logs \
    && mkdir -p /var/lib/nginx/tmp \
    && chown -R nonroot:nonroot /run/nginx \
    && chown -R nonroot:nonroot /var/lib/nginx

# Copia os arquivos do projeto para o container
WORKDIR /var/www/html
COPY --chown=nonroot:nonroot ../../composer.json ./
COPY --chown=nonroot:nonroot ../../composer.lock ./
COPY --chown=nonroot:nonroot ../../src ./src
COPY --chown=nonroot:nonroot ../../public ./public
COPY --chown=nonroot:nonroot .env ./

# Define a pasta /var/www/html/public como a raiz do Nginx e PHP-FPM
ENV NGINX_DOCUMENT_ROOT=/var/www/html/public \
    PHP_FPM_DOCUMENT_ROOT=/var/www/html/public

# Instala o Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader \
    && composer clear-cache

# Configura o Nginx
COPY --chown=nonroot:nonroot Docker/prod/nginx.conf /etc/nginx/nginx.conf

# Altera as permissões do diretório /var/www/html
RUN chown -R nonroot:nonroot /var/www/html

# Expõe a porta 80 para acesso externo
EXPOSE 80

# Muda para o usuário nonroot
USER nonroot

# Inicia o Nginx e o PHP-FPM
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
