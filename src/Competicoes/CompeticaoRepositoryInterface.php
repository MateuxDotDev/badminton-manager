<?php

namespace App\Competicoes;

interface CompeticaoRepositoryInterface
{
    function todasAsCompeticoes(): array;

    function competicoesAbertas(): array;

    function criarCompeticao(Competicao $competicao): int;

    function alterarCompeticao(Competicao $competicao): bool;

    function excluirCompeticao(int $id): void;

    function getViaId(int $id): ?Competicao;
}
