<?php

namespace App\Atleta;

interface AtletaCompeticaoRepositoryInterface
{
    function getAtletasForaCompeticaoViaNome(int $idCompeticao, string $nomeAtleta): ?Atleta;
}