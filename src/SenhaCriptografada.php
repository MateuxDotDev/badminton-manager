<?php

namespace App;

class SenhaCriptografada
{
    public function __construct(
        private readonly string $hash,
        private readonly string $salt,
    ) {}

    public function hash(): string
    {
        return $this->hash;
    }

    public function salt(): string
    {
        return $this->salt;
    }

    // TODO renomear? ::existente()?
    public static function from(?string $hash, ?string $salt): ?SenhaCriptografada
    {
        if ($hash !== null && $salt !== null) {
            return new SenhaCriptografada($hash, $salt);
        }
        return null;
    }

    private static function gerarSalt()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $salt = '';
        for ($i = 0; $i < random_int(32, 64); $i++) {
            $salt .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $salt;
    }

    public static function criptografar(string $usuario, string $senha, int $cost=12): SenhaCriptografada
    {
        $salt = self::gerarSalt();
        $toHash = $usuario . $senha . $salt;
        $hash = password_hash($toHash, PASSWORD_BCRYPT, ['cost' => $cost]);
        return SenhaCriptografada::from($hash, $salt);
    }

    public function validar(string $usuario, string $senha): bool
    {
        $toHash = $usuario . $senha . $this->salt;
        return password_verify($toHash, $this->hash);
    }
}