<?php

namespace App\Tecnico\Atleta;

interface AtletaRepositoryInterface
{
    function criarAtleta(Atleta $atleta): int;
}