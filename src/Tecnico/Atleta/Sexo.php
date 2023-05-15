<?php

namespace App\Tecnico\Atleta;

enum Sexo: string
{
    case MASCULINO = 'Masculino';
    case FEMININO = 'Feminino';
    case NAO_DECLARADO = 'Não Declarado';
}
