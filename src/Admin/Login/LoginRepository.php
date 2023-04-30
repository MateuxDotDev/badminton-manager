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
    public function validateLogin(Login $login): bool
    {
        // TODO remover e usar o Login->validar no lugar 
        $stmt = $this->pdo->prepare('
            SELECT hash_senha,
                   salt_senha
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

        return password_verify($login->getBeforeHash($user['salt_senha']), $user['hash_senha']);
    }
}
