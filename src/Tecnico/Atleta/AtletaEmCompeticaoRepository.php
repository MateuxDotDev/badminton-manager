<?php

namespace App\Tecnico\Atleta;

use App\Categorias\Categoria;
use \PDO;

readonly class AtletaEmCompeticaoRepository
{
    private function __construct(
        private PDO $pdo,
    ) {}

    public function get(int $idAtleta, int $idCompeticao): ?AtletaEmCompeticao
    {
        $pdo = $this->pdo;

        $sql = <<<SQL
            SELECT ac.atleta_id
                 , a.tecnico_id
                 , a.sexo
                 , jsonb_agg(distinct acs.sexo_dupla) as sexo_dupla
                 , jsonb_agg(distinct jsonb_build_object(
                    'id', c.id,
                    'descricao', c.descricao
                 )) as categorias
              FROM atleta_competicao ac
              JOIN atleta a
                ON a.id = ac.atleta_id
              JOIN atleta_competicao_categoria acc
                ON (acc.atleta_id, acc.competicao_id) = (ac.atleta_id, ac.competicao_id)
              JOIN categoria c
                ON c.id = acc.categoria_id
              JOIN atleta_competicao_categoria acs
                ON (acs.atleta_id, acs.competicao_id) = (ac.atleta_id, ac.competicao_id)
             WHERE ac.atleta_id = :idAtleta
               AND ac.competicao_id = :idCompeticao
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'idAtleta' => $idAtleta,
            'idCompeticao' => $idCompeticao,
        ]);
        $rows = $stmt->fetchAll();

        if (count($rows) != 1) {
            return null;
        }
        $row = $rows[0];

        $atleta = (new AtletaEmCompeticao)
            ->setIdAtleta((int) $row['atleta_id'])
            ->setIdTecnico((int) $row['tecnico_id'])
            ->setSexoAtleta(Sexo::from($row['sexo']))
            ;

        foreach (json_decode($row['categorias']) as $a) {
            $categoria = new Categoria((int) $a['id'], $a['descricao'], null, null);
            $atleta->addCategoria($categoria);
        }

        foreach (json_decode($row['sexo_dupla']) as $s) {
            $sexo = Sexo::from($s);
            $atleta->addSexoDupla($sexo);
        }

        return $atleta;
    }
}