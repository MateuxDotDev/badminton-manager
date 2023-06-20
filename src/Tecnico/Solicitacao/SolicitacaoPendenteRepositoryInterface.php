<?php

namespace App\Tecnico\Solicitacao;

interface SolicitacaoPendenteRepositoryInterface
{
    function getViaIds(
        int $idCompeticao,
        int $idAtleta1,
        int $idAtleta2,
        int $idCategoria
    ): ?SolicitacaoPendente;

    function getViaTecnico(int $idTecnico): array;

    function enviar(EnviarSolicitacaoDTO $solicitacao): int;
}
