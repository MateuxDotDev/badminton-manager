<?php

namespace App\Notificacao;

enum TipoNotificacao: string
{
    case ATLETA_INCLUIDO_NA_COMPETICAO = 'atleta_incluido_na_competicao';
    case SOLICITACAO_ENVIADA = 'solicitacao_enviada';
    case SOLICITACAO_RECEBIDA = 'solicitacao_recebida';
    case SOLICITACAO_ENVIADA_REJEITADA = 'solicitacao_enviada_rejeitada';
    case SOLICITACAO_RECEBIDA_REJEITADA = 'solicitacao_recebida_rejeitada';
    case SOLICITACAO_ENVIADA_ACEITA = 'solicitacao_enviada_aceita';
    case SOLICITACAO_RECEBIDA_ACEITA = 'solicitacao_recebida_aceita';
    case DUPLA_DESFEITA_PELO_OUTRO = 'dupla_desfeita_pelo_outro';
    case DUPLA_DESFEITA_POR_VOCE = 'dupla_desfeita_por_voce';
}