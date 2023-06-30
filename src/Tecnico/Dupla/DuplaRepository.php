<?php

namespace App\Tecnico\Dupla;

use App\Tecnico\Atleta\Sexo;
use App\Util\Exceptions\ValidatorException;
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
    ): int
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

        return $this->pdo->lastInsertId();
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

    /**
     * @throws ValidatorException
     */
    public function desfazer(int $idDupla, int $idTecnico): bool
    {
        $sql = <<<SQL
            DELETE FROM dupla
                  WHERE id = :id
                    AND EXISTS (
                        SELECT 1
                          FROM atleta a
                          JOIN tecnico t
                            ON t.id = a.tecnico_id
                         WHERE a.id IN (dupla.atleta1_id, dupla.atleta2_id)
                           AND t.id = :tecnico_id
                    )
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $idDupla,
            'tecnico_id' => $idTecnico
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @throws ValidatorException
     */
    public function getViaId(int $id): Dupla
    {
        $sql = <<<SQL
            SELECT d.id,
                   d.solicitacao_id AS "idSolicitacao",
                   d.criado_em AS "criadoEm",
                   c.id AS "categoriaId",
                   c.descricao AS categoria,
                   co.nome AS competicao,
                   co.id AS "competicaoId",
                   jsonb_agg(
                       jsonb_build_object(
                           'id', a.id,
                           'nome', a.nome_completo,
                           'sexo', a.sexo,
                           'dataNascimento', a.data_nascimento,
                           'foto', a.path_foto,
                           'informacoes', a.informacoes,
                           'tecnico', json_build_object(
                               'id', t.id,
                               'nome', t.nome_completo,
                               'email', t.email,
                               'informacoes', t.informacoes,
                               'clubeId', cl.id,
                               'clube', cl.nome
                           )
                       )
                   ) AS "atletas"
              FROM dupla d
              JOIN atleta a
                ON a.id IN (d.atleta1_id, d.atleta2_id)
              JOIN tecnico t
                ON t.id = a.tecnico_id
              JOIN competicao co
                ON co.id = d.competicao_id
              JOIN categoria c
                ON c.id = d.categoria_id
              JOIN clube cl
                ON cl.id = t.clube_id
             WHERE d.id = :id
          GROUP BY 1, 2, 3, 4, 5, 6, 7
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $id
        ]);

        return Dupla::fromRow($stmt->fetch());
    }
}
