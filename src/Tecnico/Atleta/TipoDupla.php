<?php

namespace App\Tecnico\Atleta;

enum TipoDupla
{
    case MASCULINA;
    case FEMININA;
    case MISTA;

    public function toString(): string
    {
        return match ($this) {
            self::MASCULINA => 'masculina',
            self::FEMININA => 'feminina',
            self::MISTA => 'mista',
        };
    }

    public static function criar(Sexo $a, Sexo $b): TipoDupla
    {
        if ($a != $b) {
            return self::MISTA;
        } elseif ($a == Sexo::MASCULINO) {
            return self::MASCULINA;
        }

        return self::FEMININA;
    }
}
