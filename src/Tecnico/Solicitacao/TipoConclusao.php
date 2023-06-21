<?php

namespace App\Tecnico\Solicitacao;

enum TipoConclusao
{
    case ACEITA;
    case REJEITADA;
    case CANCELADA;
}