<?php

namespace App\Tecnico\Solicitacao;

interface SolicitacaoPendenteRepositoryInterface
{
    function getEnvolvendo(
        int $idCompeticao,
        int $idAtleta1,
        int $idAtleta2,
        int $idCategoria
    ): ?SolicitacaoPendente;

    function enviar(EnviarSolicitacaoDTO $solicitacao): int;
}
