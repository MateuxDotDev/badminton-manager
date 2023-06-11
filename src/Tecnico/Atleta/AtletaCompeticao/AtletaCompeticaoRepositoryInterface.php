<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

interface AtletaCompeticaoRepositoryInterface
{
    function getAtletaCompeticao($idTecnico, $idCompeticao) : array;

    function getAtletasForaCompeticao(int $idTecnico, int $idCompeticao);

    function cadastrarAtletaCompeticao(AtletaCompeticao $atletaCompeticao) : bool;

    function getViaId(int $idAtleta, int $idCompeticao): ?array;
}
