<?php

namespace App\Tecnico\Atleta;

interface AtletaRepositoryInterface
{
    function criarAtleta(Atleta $atleta): int;

    function getViaTecnico(int $tecnicoId): array;

    function removerAtleta(int $atletaId): bool;

    function atualizarAtleta(Atleta $atleta): bool;
}
