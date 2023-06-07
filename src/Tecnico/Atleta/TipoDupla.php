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
            self::MASCULINA => 'Masculina',
            self::FEMININA => 'Feminina',
            self::MISTA => 'Mista',
        };
    } 

    public static function criar(Sexo $a, Sexo $b)
    {
        if ($a != $b) return self::MISTA;
        if ($a == Sexo::MASCULINO) return self::MASCULINA;
        return self::FEMININA;
    }
}