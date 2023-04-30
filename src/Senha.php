<?php

namespace App;

class Senha
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

    public static function from(?string $hash, ?string $salt): ?Senha
    {
        if ($hash !== null && $salt !== null) {
            return new Senha($hash, $salt);
        }
        return null;
    }
}