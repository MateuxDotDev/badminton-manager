<?php

namespace App\Util\General;

enum Ordenacao: string {
    case ASC = 'ASC';
    case DESC = 'DESC';

    public static function fromString(string $s): ?Ordenacao {
        $s = mb_strtolower(trim($s));
        return match ($s) {
            'asc'  => self::ASC,
            'desc' => self::DESC,
            default => null
        };
    }

    public function inversa(): Ordenacao
    {
        return match ($this) {
            self::ASC  => self::DESC,
            self::DESC => self::ASC,
        };
    }
}