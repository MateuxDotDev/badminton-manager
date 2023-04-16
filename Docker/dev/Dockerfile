# Imagem base oficial do PHP 8.1 com Apache
FROM php:8.1-apache

# Criar um grupo e usuário nonroot
RUN groupadd -r nonroot && useradd -r -g nonroot nonroot

# Atualiza os pacotes e instala as dependências necessárias
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends libpq-dev git zip unzip \
    && docker-php-ext-install pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Configura o 'ServerName' no arquivo de configuração do Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Instala o Xdebug e habilita-o
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configura o Xdebug
COPY ./Docker/dev/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Desativa o Xdebug temporariamente
RUN sed -i 's/xdebug.mode=debug/xdebug.mode=off/g' /usr/local/etc/php/conf.d/xdebug.ini

# Copia os arquivos do projeto para o container
COPY --chown=nonroot:nonroot ./phpunit.xml /var/www/html/
COPY --chown=nonroot:nonroot ./.env /var/www/html/

# Instala o Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Altera as permissões do diretório /var/www/html
RUN chmod -R ug+rwx /var/www/html

# Reativa o Xdebug
RUN sed -i 's/xdebug.mode=off/xdebug.mode=debug/g' /usr/local/etc/php/conf.d/xdebug.ini

# Define a pasta /var/www como a raiz do Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/src

# Altera o DocumentRoot do Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Expõe a porta 80 para acesso externo
EXPOSE 80

# Muda para o usuário nonroot
USER nonroot
