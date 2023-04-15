# Gerenciador de Badminton

Aqui está presente o código fonte do projeto MatchPoint, um sistema responsável por gerenciar duplas de competições de badminton. 

# Desenvolvimento

Este projeto utiliza Docker, PHP 8.1 e Xdebug para facilitar o desenvolvimento. Siga as instruções abaixo para configurar o ambiente e começar a desenvolver.

## Pré-requisitos

- Docker: https://docs.docker.com/get-docker/
- Visual Studio Code (opcional): https://code.visualstudio.com/

## Configuração do ambiente

1. Clone este repositório e navegue até a pasta do projeto no terminal ou prompt de comando.

2. Construa a imagem Docker e inicie os contêineres com o seguinte comando:

```bash
docker compose --file Docker/dev/docker-compose.yml --env-file .env up -d 
```

Caso queira parar os contêineres, execute o seguinte comando:

```bash
docker compose --file Docker/dev/docker-compose.yml --env-file .env down
```

## Configurando o Composer

### Como executar o Composer

Este projeto utiliza o composer como gerenciador de pacotes. Entretanto ele está disponível apenas via Docker.
Para que possa utilizar os comandos disponíveis do mesmo, basta se conectar ao container da aplicação após sua inicalização com o comando a seguir:

```bash
docker exec -it --user nonroot badminton-web /bin/sh
```

Para mais informações, acesse a documentação do Composer disponível em: https://getcomposer.org/doc/01-basic-usage.md

### Instalando as dependências do projeto

Para instalar as dependências do projeto, basta se conectar com o contâiner conforme descrito na etapa acima, e posteriormente executar o comando:

```bash
composer install
```

Qualquer outro comnado executado com o Composer, precisará ser feito via docker.

### Atualizando o `composer.lock`

Caso altere o `composer.lock` no contâiner, para importar ele novamente para o root do projeto, basta executar:

```bash
docker cp badminton-web:/var/www/html/composer.lock ./composer.lock
```

O ambiente de desenvolvimento agora deve estar acessível através do endereço http://localhost:8080 no seu navegador.

## Configurar Xdebug no Visual Studio Code (opcional)

Se você deseja usar o Xdebug com o Visual Studio Code, siga estas instruções:

1. Instale a extensão "PHP Debug" no VSCode: https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug

2. Crie uma pasta chamada ".vscode" na raiz do projeto, se ainda não existir.

3. Dentro da pasta ".vscode", crie um arquivo chamado "launch.json" e adicione o seguinte conteúdo:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}"
      },
      "log": true,
      "externalConsole": false,
      "stopOnEntry": false
    }
  ]
}
```

4. Reinicie o VSCode, se necessário.

5. No VSCode, clique no ícone de depuração (um inseto) na barra lateral esquerda e selecione "Listen for Xdebug" no menu suspenso na parte superior.

6. Clique no botão de reprodução verde (▶️) para iniciar a sessão de depuração. O VSCode começará a ouvir conexões do Xdebug.

# Parar e remover os contêineres

Para parar e remover os contêineres Docker, execute o seguinte comando no mesmo diretório do arquivo docker-compose.yml:

```bash
docker compose down
```

## Configuração do arquivo .env

1. Crie um arquivo chamado `.env` na raiz do projeto.

2. Adicione as com base no arquivo `example.env`.

## Realizar build de Produção

Para realizar o build de produção e verificar que tudo está funcionando corretamente, execute o seguinte comando:

```bash
docker build -t mateuxlucax/badminton-manager -f Docker/prod/Dockerfile . 
```

E para executar o container:

```bash
docker run -d -p 8080:80 --name badminton-manager mateuxlucax/badminton-manager
```