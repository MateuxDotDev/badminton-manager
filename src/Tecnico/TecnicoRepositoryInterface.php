<?php

namespace App\Tecnico;

interface TecnicoRepositoryInterface
{
    function getViaEmail(string $email): ?Tecnico;
    function getViaId(int $id): ?Tecnico;
    function criarTecnico(Tecnico $tecnico): void;
}