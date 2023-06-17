<?php

namespace App\Tecnico\Atleta;

enum ResultadoCompatibilidade
{
    case OK;
    case CATEGORIA_INCOMPATIVEL;
    case SEXO_INCOMPATIVEL;
    case MESMO_TECNICO;
}
