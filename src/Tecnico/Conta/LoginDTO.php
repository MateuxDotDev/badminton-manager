<?php

namespace App\Tecnico\Conta;

use App\Util\Exceptions\ValidatorException;
use App\Util\Http\HttpStatus;

readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $senha,
    ) {}

    /**
     * @throws ValidatorException
     */
    public static function parse(array $a): LoginDTO
    {
        if (!array_key_exists('email', $a)) {
            throw new ValidatorException("Campo 'e-mail' faltando", HttpStatus::BAD_REQUEST);
        }
        if (!array_key_exists('senha', $a)) {
            throw new ValidatorException("Campo 'senha' faltando", HttpStatus::BAD_REQUEST);
        }

        $email = filter_var($a['email'], FILTER_SANITIZE_EMAIL);
        $senha = $a['senha'];

        return new LoginDTO($email, $senha);
    }
}
