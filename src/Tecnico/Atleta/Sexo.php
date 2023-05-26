<?php

namespace App\Tecnico\Atleta;

enum Sexo: string
{
    case MASCULINO = 'M';
    case FEMININO = 'F';

    public function toString(): string
    {
        return match ($this) {
            self::MASCULINO => 'Masculino',
            self::FEMININO => 'Feminino',
        };
    }
}
