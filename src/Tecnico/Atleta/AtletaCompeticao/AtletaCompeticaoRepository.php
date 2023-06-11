<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\Sexo;
use App\Util\General\Dates;
use PDO;

class AtletaCompeticaoRepository implements AtletaCompeticaoRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    public function getAtletaCompeticao($idTecnico, $idCompeticao) : array
    {
        $sql = <<<SQL
                SELECT atleta.id as id,
                    atleta.nome_completo as nome_completo,
                    atleta.sexo as sexo,
                    atleta.data_nascimento as data_nascimento,
                    atleta.informacoes as informacoes,
                    atleta.path_foto as path_foto,
                    atleta.criado_em as criado_em,
                    atleta.alterado_em as alterado_em,
                    atleta_competicao.competicao_id as competicao_id
                FROM atleta
                JOIN atleta_competicao
                  ON atleta.id = atleta_competicao.atleta_id
                WHERE atleta.tecnico_id = $idTecnico
                AND atleta_competicao.competicao_id = $idCompeticao
            SQL;

        $query = $this->pdo->query($sql);
        $atletas = [];
        foreach ($query as $linha) {
            $atleta = $this->createAtletaFromRow($linha);

            $competicao = (new Competicao())->setId($linha['competicao_id']);

            $atletas[] = (new AtletaCompeticao())
                ->setAtleta($atleta)
                ->setCompeticao($competicao);
        }
        return $atletas;
    }

    public function getAtletasForaCompeticao(int $idTecnico, int $idCompeticao): array
    {
        $sql = <<<SQL
                SELECT atleta.id as id,
                    atleta.nome_completo as nome_completo,
                    atleta.sexo as sexo,
                    atleta.data_nascimento as data_nascimento,
                    atleta.informacoes as informacoes,
                    atleta.path_foto as path_foto,
                    atleta.criado_em as criado_em,
                    atleta.alterado_em as alterado_em
                FROM atleta
                WHERE atleta.tecnico_id = $idTecnico
                AND atleta.id NOT IN(
                    SELECT atleta_competicao.atleta_id
                      FROM atleta_competicao
                     WHERE atleta_competicao.competicao_id = $idCompeticao
                )
            SQL;
        $query = $this->pdo->query($sql);
        $atletas = [];
        foreach ($query as $linha) {
            $atletas[] = $this->createAtletaFromRow($linha);
        }
        return $atletas;
    }

    public function cadastrarAtletaCompeticao(AtletaCompeticao $atletaCompeticao) : bool
    {
        $sql = <<<SQL
            INSERT INTO atleta_competicao (
                atleta_id,
                competicao_id,
                informacoes
            )
            VALUES (
                :atleta_id,
                :competicao_id,
                :informacoes
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'atleta_id' => $atletaCompeticao->atleta()->id(),
            'competicao_id' => $atletaCompeticao->competicao()->id(),
            'informacoes' => $atletaCompeticao->informacao()
        ]);
    }

    public function getViaId(int $idAtleta, int $idCompeticao): ?array
    {
        /**
         * TODO
         * por enquanto traz somente as informações que são usadas para
         * pré-preencher os filtros, mas também poderia trazer em cima os
         * dados do atleta para visualizar qual atleta está sendo considerado
         * (no mínimo o nome -- "Mostrando atletas compatíveis com $nome")
         */

        $sql = <<<SQL
              SELECT a.sexo,
                     jsonb_agg(distinct acc.categoria_id) as categorias,
                     jsonb_agg(distinct acs.sexo_dupla) as sexo_duplas
                FROM atleta a
                JOIN atleta_competicao ac
                  ON ac.atleta_id = :idAtleta
                 AND ac.competicao_id = :idCompeticao
                JOIN atleta_competicao_categoria acc
                  ON acc.atleta_id = ac.atleta_id
                 AND acc.competicao_id = ac.competicao_id
                JOIN atleta_competicao_sexo_dupla acs
                  ON acs.atleta_id = ac.atleta_id
                 AND acs.competicao_id = ac.competicao_id
            GROUP BY a.id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'idAtleta'     => $idAtleta,
            'idCompeticao' => $idCompeticao,
        ]);

        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return null;
        }

        $row = $rows[0];

        return [
            'sexo'       => Sexo::from($row['sexo']),
            'categorias' => json_decode($row['categorias']),
            'sexoDuplas' => array_map(fn($s) => Sexo::from($s), json_decode($row['sexo_duplas']))
        ];
    }

    private function createAtletaFromRow(array $row): Atleta
    {
        return (new Atleta())
            ->setId($row['id'])
            ->setNomeCompleto($row['nome_completo'])
            ->setSexo(Sexo::from($row['sexo']))
            ->setDataNascimento(Dates::parseDay($row['data_nascimento']))
            ->setInformacoesAdicionais($row['informacoes'])
            ->setDataCriacao(Dates::parseMicro($row['criado_em']))
            ->setDataAlteracao(Dates::parseMicro($row['alterado_em']))
            ->setFoto($row['path_foto']);
    }
}
