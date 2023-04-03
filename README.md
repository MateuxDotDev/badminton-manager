# Gerenciador de Badminton

...

# Desenvolvimento

Este projeto utiliza Docker, PHP 8.1 e Xdebug para facilitar o desenvolvimento. Siga as instruções abaixo para configurar o ambiente e começar a desenvolver.

## Pré-requisitos

- Docker: https://docs.docker.com/get-docker/
- Docker Compose: https://docs.docker.com/compose/install/
- Visual Studio Code (opcional): https://code.visualstudio.com/

## Configuração do ambiente

1. Clone este repositório e navegue até a pasta do projeto no terminal ou prompt de comando.

2. Construa a imagem Docker e inicie os contêineres com o seguinte comando:

```bash
docker-compose up -d
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
docker-compose down
```