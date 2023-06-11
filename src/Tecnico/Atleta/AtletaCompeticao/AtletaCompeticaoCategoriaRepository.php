<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use PDO;

class AtletaCompeticaoCategoriaRepository implements AtletaCompeticaoCategoriaRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    public function cadastrarAtletaCompeticaoCategoria(AtletaCompeticaoCategoria $atletaCompeticaoCategoria): bool
    {
        $sql = <<<SQL
            INSERT INTO atleta_competicao_categoria (
                atleta_id,
                competicao_id,
                categoria_id
            )
            VALUES (
                :atleta_id,
                :competicao_id,
                :categoria_id
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'atleta_id' => $atletaCompeticaoCategoria->atletaCompeticao()->atleta()->id(),
            'competicao_id' => $atletaCompeticaoCategoria->atletaCompeticao()->competicao()->id(),
            'categoria_id' => $atletaCompeticaoCategoria->categoria()->id()
        ]);
    }
}
