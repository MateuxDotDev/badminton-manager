<?php

namespace App\Admin\Login;
use App\Senha;

// TODO acho que dá pra refatorar pra ser só métodos estáticos
// e substituir o LoginRepository por um AdminRepository
// com uma classe Admin que usa o trait PodeTerSenhaImpl

class Login
{
    private string $usuario;
    private string $senha;

    public function __construct(string $usuario, string $senha)
    {
        $this->usuario = $usuario;
        $this->senha = $senha;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function getUsuario(): string
    {
        return $this->usuario;
    }

    private function getBeforeHash(string $salt): string
    {
        return $this->usuario . $this->senha . $salt;
    }

    public static function gerarSalt()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $salt = '';

        for ($i = 0; $i < random_int(32, 64); $i++) {
            $salt .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $salt;
    }

    public function gerarHash(string $salt, int $cost = 12): string
    {
        return password_hash($this->getBeforeHash($salt), PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    public function validar(?Senha $senhaCorreta): bool
    {
        return $senhaCorreta != null
             && password_verify($this->getBeforeHash($senhaCorreta->salt()), $senhaCorreta->hash());
    }
}
