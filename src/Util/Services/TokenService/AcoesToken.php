<?php

namespace App\Util\Services\TokenService;

enum AcoesToken: string
{
    case REMOVER_ATLETA = 'removerAtleta';
    case ALTERAR_ATLETA = 'alterarAtleta';
    case ACEITAR_SOLICITACAO = 'alterarSolicitacao';
    case REJEITAR_SOLICITACAO = 'rejeitarSolicitacao';
}
