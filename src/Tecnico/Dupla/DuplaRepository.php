<?php

namespace App\Tecnico\Dupla;

use PDO;

class DuplaRepository
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    public function criarDupla(
        int $idCompeticao,
        int $idAtleta1,
        int $idAtleta2,
        int $idCategoria,
        int $idSolicitacaoOrigem,
    ): void
    {
        $sql = <<<SQL
            INSERT INTO dupla
            (competicao_id, categoria_id, atleta1_id, atleta2_id, solicitacao_id)
            VALUES
            (:competicao_id, :categoria_id, :atleta1_id, :atleta2_id, :solicitacao_id)
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'competicao_id'  => $idCompeticao,
            'categoria_id'   => $idCategoria,
            'atleta1_id'     => $idAtleta1,
            'atleta2_id'     => $idAtleta2,
            'solicitacao_id' => $idSolicitacaoOrigem,
        ]);
    }
}
