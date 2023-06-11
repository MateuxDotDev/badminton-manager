<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use PDO;

class AtletaCompeticaoDuplaRepository implements AtletaCompeticaoDuplaRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    public function cadastrarAtletaCompeticaoDupla(AtletaCompeticaoDupla $acd): bool
    {
        $sql = <<<SQL
            INSERT INTO atleta_competicao_sexo_dupla (
                atleta_id,
                competicao_id,
                sexo_dupla
            )
            VALUES (
                :atleta_id,
                :competicao_id,
                :sexo_dupla
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'atleta_id' => $acd->atletaCompeticao()->atleta()->id(),
            'competicao_id' => $acd->atletaCompeticao()->competicao()->id(),
            'sexo_dupla' => $acd->tipoDupla()->value
        ]);
    }
}
