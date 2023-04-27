<?php

namespace App\Admin\Login;



use Exception;
use PDO;

readonly class LoginRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @throws Exception
     */
    function validateLogin(Login $login): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT hash_senha, salt_senha
            FROM admin
            WHERE "user" = :user
        ');
        $usuario = $login->getUsuario();
        $stmt->bindParam(':user', $usuario);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        return password_verify($login->gerarHash($user['salt_senha']), $user['hash_senha']);
    }
}
