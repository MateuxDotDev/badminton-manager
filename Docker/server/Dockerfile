# Use a imagem oficial do PHP 8.2 Alpine
FROM php:8.2-alpine3.18

# Instale as dependências necessárias e o driver PDO para PostgreSQL
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql

# Crie um grupo e usuário para executar nosso aplicativo
RUN addgroup -g 1000 nonroot && adduser -u 1000 -G nonroot -D nonroot

# Mude para o novo usuário
USER nonroot

# Defina o diretório de trabalho no container
WORKDIR /var/www/html

# Copie os scripts para o diretório de trabalho no container
COPY --chown=nonroot:nonroot ../../scripts ./scripts
COPY --chown=nonroot:nonroot ../../src ./src

WORKDIR /var/www/html/scripts

# Execute o script de inicialização quando o container iniciar
CMD ["php", "migrate.php"]
CMD ["php", "createAdmin.php", "admin", "7!k*dV2WTUtUk6BKPyWujj!L"]