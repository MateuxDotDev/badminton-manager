<?php

namespace App\Tecnico\Conta;
use App\Result;

readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $senha,
    ) {}

    public static function parse(array $a): LoginDTO|string
    {
        if (!array_key_exists('email', $a)) return 'E-mail faltando';
        if (!array_key_exists('senha', $a)) return 'Senha faltando';

        $email = filter_var($a['email'], FILTER_SANITIZE_EMAIL);
        $senha = $a['senha'];

        return new LoginDTO($email, $senha);
    }
}