# Imagem base oficial do PHP 8.2 com Apache
FROM php:8.2-apache

# Criar um grupo e usuário nonroot e instala as dependências necessárias
RUN groupadd -r nonroot && useradd -r -g nonroot -m nonroot \
    && apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends libpq-dev git zip unzip fish \
    && docker-php-ext-install pdo_pgsql opcache \
    && echo 'opcache.enable=1' >> /usr/local/etc/php/conf.d/opcache.ini \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && pecl install xdebug && docker-php-ext-enable xdebug \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod -R ug+rwx /var/www/html \
    && chown -R nonroot:nonroot /var/www/html \
    && chsh -s /usr/bin/fish nonroot

# Configura o Xdebug
COPY ./Docker/dev/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Define a pasta /var/www como a raiz do Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Altera o DocumentRoot do Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Expõe a porta 80 para acesso externo
EXPOSE 80

# Muda para o usuário nonroot
USER nonroot
