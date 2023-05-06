<?php

namespace App;

use Exception;

readonly class SenhaCriptografada
{
    public function __construct(
        private string $hash,
        private string $salt,
    ) {}

    public function hash(): string
    {
        return $this->hash;
    }

    public function salt(): string
    {
        return $this->salt;
    }

    public static function existente(?string $hash, ?string $salt): ?SenhaCriptografada
    {
        if ($hash !== null && $salt !== null) {
            return new SenhaCriptografada($hash, $salt);
        }
        return null;
    }

    /**
     * @throws Exception
     */
    private static function gerarSalt(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $salt = '';
        for ($i = 0; $i < random_int(32, 64); $i++) {
            $salt .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $salt;
    }

    /**
     * @throws Exception
     */
    public static function criptografar(string $usuario, string $senha, int $cost = 12): SenhaCriptografada
    {
        $salt = self::gerarSalt();
        $toHash = $usuario . $senha . $salt;
        $hash = password_hash($toHash, PASSWORD_BCRYPT, ['cost' => $cost]);
        return SenhaCriptografada::existente($hash, $salt);
    }

    public function validar(string $usuario, string $senha): bool
    {
        $toHash = $usuario . $senha . $this->salt;
        return password_verify($toHash, $this->hash);
    }
}
