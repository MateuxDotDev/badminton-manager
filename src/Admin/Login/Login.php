<?php

namespace App\Admin\Login;

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

    public function getBeforeHash(string $salt): string
    {
        return $this->usuario . $this->senha . $salt;
    }

    public function gerarHash(string $salt): string
    {
        return password_hash($this->getBeforeHash($salt), PASSWORD_BCRYPT, ['cost' => 20]);
    }
}
