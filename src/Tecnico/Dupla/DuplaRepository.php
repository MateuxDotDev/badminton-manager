<?php

namespace App\Tecnico\Dupla;

use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Atleta\TipoDupla;
use App\Util\General\Dates;
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

    /**
     * Retorna se o atleta tem uma dupla do sexo informado jogando na categoria informada nessa competição.
     */
    public function temDupla(int $idCompeticao, int $idAtleta, int $idCategoria, Sexo $sexo): bool
    {
        $sql = <<<SQL
            SELECT 1
              FROM dupla d
              JOIN atleta a1
                ON a1.id = d.atleta1_id
              JOIN atleta a2
                ON a2.id = d.atleta2_id
             WHERE d.competicao_id = :competicao_id
               AND d.categoria_id = :categoria_id
               AND ((d.atleta1_id = :atleta_id AND a2.sexo = :sexo) OR
                    (d.atleta2_id = :atleta_id AND a1.sexo = :sexo))
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'competicao_id' => $idCompeticao,
            'categoria_id'  => $idCategoria,
            'atleta_id'     => $idAtleta,
            'sexo'          => $sexo->value
        ]);

        return $stmt->rowCount() > 0;
    }

    public function formadas(int $idCompeticao): array
    {
        $sql = <<<SQL
            SELECT d.id,
                   d.solicitacao_id AS idSolicitacao,
                   c.descricao AS categoria,
                   jsonb_agg(
                       jsonb_build_object(
                           'id', a.id,
                           'nome', a.nome_completo,
                           'sexo', a.sexo,
                           'dataNascimento', a.data_nascimento,
                           'foto', a.path_foto,
                           'tecnico', json_build_object(
                               'id', t.id,
                               'nome', t.nome_completo,
                               'clube', cl.nome
                           )
                       )
                   ) AS "atletas"
              FROM dupla d
              JOIN atleta a
                ON a.id IN (d.atleta1_id, d.atleta2_id)
              JOIN tecnico t
                ON t.id = a.tecnico_id
              JOIN categoria c
                ON c.id = d.categoria_id
              JOIN clube cl
                ON cl.id = t.clube_id
             WHERE d.competicao_id = :competicao_id
          GROUP BY d.id, d.solicitacao_id, c.descricao
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['competicao_id' => $idCompeticao]);
        $rows = $stmt->fetchAll();
        $duplas = [];

        foreach ($rows as $row) {
            $atletas = json_decode($row['atletas'], true);
            foreach ($atletas as &$atleta) {
                $dataNascimento = Dates::parseDay($atleta['dataNascimento']);
                $atleta['idade'] = Dates::age($dataNascimento);
                $atleta['dataNascimento'] = Dates::formatDayBr($dataNascimento);
            }
            unset($atleta);

            $duplas[] = [
                ...$row,
                'atletas' => $atletas,
            ];
        }

        return $duplas;
    }
}
