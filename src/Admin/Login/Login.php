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

    public function gerarHash(string $salt): string
    {
        return password_hash($this->usuario . $this->senha . $salt, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
