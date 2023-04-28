<?php

/*
O arquivo createAdmin.php é um script responsável pela criação de um usuário administrativo em um banco de dados PostgreSQL.

O script possui duas funções principais:

1. generateSalt(): Essa função gera uma string aleatória de comprimento variável entre 32 e 64 caracteres, que será usada como salt para a geração do hash da senha do usuário.

2. generateAdmin($username, $password): Essa função recebe um nome de usuário e uma senha como parâmetros, gera um salt usando a função generateSalt() e cria um hash da senha usando a função PHP password_hash(). A função então insere o nome do usuário, o hash da senha e o salt na tabela 'admin' do banco de dados.

O script é executado a partir da linha de comando, com o nome do usuário e a senha fornecidos como argumentos. Se o nome do usuário ou a senha não forem fornecidos, o script emitirá uma mensagem de erro e encerrará a execução. Se a função generateAdmin() retornar verdadeiro, o script emitirá uma mensagem indicando que o usuário admin foi criado com sucesso. Se a função retornar falso, o script emitirá uma mensagem de erro.

Exemplo de uso:
php createAdmin.php nome_do_usuario senha_do_usuario
Este comando cria um usuário admin com o nome 'nome_do_usuario' e a senha 'senha_do_usuario'.
*/

require_once __DIR__ . '/initPDO.php';
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @throws Exception
 */
function generateSalt(): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $salt = '';

    for ($i = 0; $i < random_int(32, 64); $i++) {
        $salt .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $salt;
}

/**
 * @throws Exception
 */
function generateAdmin(string $username, string $password): bool
{
    $pdo = initPdo();
    $salt = generateSalt();
    $login = new App\Admin\Login\Login($username, $password);
    echo "Gerando hash da senha...\n";
    $hash = $login->gerarHash($salt);
    echo "Inserindo usuário no banco de dados...\n";

    $stmt = $pdo->prepare('
        INSERT INTO admin ("user", hash_senha, salt_senha)
                   VALUES (:user, :hash_senha, :salt_senha)
     ');
    $stmt->bindParam(':user', $username);
    $stmt->bindParam(':hash_senha', $hash);
    $stmt->bindParam(':salt_senha', $salt);

    return $stmt->execute();
}

if (empty($argv[1]) || empty($argv[2])) {
    echo "Error: Usuário e senha não podem ser vazios.\n";
    exit(1);
}

$username = $argv[1];
$password = $argv[2];

try {
    if (generateAdmin($username, $password)) {
        echo "Usuário '{$username}' criado com sucesso.\n";
    } else {
        echo "Erro: Não foi possível criar o usuário '{$username}'.\n";
    }
} catch (Exception $e) {
    echo "Erro: {$e->getMessage()}\n";
    exit(1);
}
