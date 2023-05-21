<?php

namespace App\Tecnico\Atleta;

enum Sexo: string
{
    case MASCULINO = 'M';
    case FEMININO = 'F';

    public function toString(): string
    {
        if ($this == self::MASCULINO) {
            return 'Masculino';
        } elseif ($this == self::FEMININO) {
            return 'Feminino';
        } else {
            return 'Não informado';
        }
    }
}
