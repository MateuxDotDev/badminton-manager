<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

interface AtletaCompeticaoDuplaRepositoryInterface
{
    function cadastrarAtletaCompeticaoDupla(AtletaCompeticaoDupla $acd): bool;
}
