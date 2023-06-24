<?php /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Categorias\Categoria;
use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\Sexo;
use App\Util\General\Dates;
use PDO;

class AtletaCompeticaoRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    public function getAtletasForaCompeticao(int $idTecnico, int $idCompeticao): array
    {
        $sql = <<<SQL
            SELECT a.id as id,
                   a.nome_completo as nome_completo,
                   a.sexo as sexo,
                   a.data_nascimento as data_nascimento,
                   a.informacoes as informacoes,
                   a.path_foto as path_foto,
                   a.criado_em as criado_em,
                   a.alterado_em as alterado_em
              FROM atleta a
             WHERE a.tecnico_id = $idTecnico
               AND a.id NOT IN (SELECT ac.atleta_id
                                  FROM atleta_competicao ac
                                 WHERE ac.competicao_id = $idCompeticao)
        SQL;
        $query = $this->pdo->query($sql);
        $atletas = [];
        foreach ($query as $linha) {
            $atletas[] = (new Atleta())
            ->setNomeCompleto($linha['nome_completo'])
            ->setSexo(Sexo::from($linha['sexo']))
            ->setDataNascimento(Dates::parseDay($linha['data_nascimento']))
            ->setInformacoesAdicionais($linha['informacoes'])
            ->setFoto($linha['path_foto'])
            ->setId($linha['id'])
            ->setDataCriacao(Dates::parseMicro($linha['criado_em']))
            ->setDataAlteracao(Dates::parseMicro($linha['alterado_em']));
        }
        return $atletas;
    }


    private function salvarCategorias(int $idAtleta, int $idCompeticao, array $categorias): void
    {
        // Deletar categorias existentes (para quando o técnico estiver alterando o cadastro de um atleta na competição)

        $sql = <<<SQL
            DELETE FROM atleta_competicao_categoria WHERE atleta_id = ? AND competicao_id = ?
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([ $idAtleta, $idCompeticao ]);


        // Inserir categorias

        $valuesArray = [];

        $parametros = [
            'atleta_id'     => $idAtleta,
            'competicao_id' => $idCompeticao,
        ];

        for ($i = 0; $i < count($categorias); $i++) {
            $categoria = $categorias[$i];
            $parametro = "categoria_id_$i";

            $parametros[$parametro] = $categoria->id();

            $valuesArray[] = "(:atleta_id, :competicao_id, :$parametro)";
        }

        $values = implode(',', $valuesArray);

        $sql = <<<SQL
            INSERT INTO atleta_competicao_categoria (atleta_id, competicao_id, categoria_id)
            VALUES $values
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
    }


    private function salvarSexoDupla(int $idAtleta, int $idCompeticao, array $sexoDupla): void
    {
        // Deletar existentes (para quando o técnico estiver alterando o cadastro de um atleta na competição)

        $sql = <<<SQL
            DELETE FROM atleta_competicao_sexo_dupla WHERE atleta_id = ? AND competicao_id = ?
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([ $idAtleta, $idCompeticao ]);


        // Inserir

        $parametros = [
            'atleta_id'     => $idAtleta,
            'competicao_id' => $idCompeticao
        ];

        $valuesArray = [];

        for ($i = 0; $i < count($sexoDupla); $i++) {
            $parametro = "sexo_id_$i";
            $parametros[$parametro] = $sexoDupla[$i]->value;

            $valuesArray[] = "(:atleta_id, :competicao_id, :$parametro)";
        }

        $values = implode(',', $valuesArray);

        $sql = <<<SQL
            INSERT INTO atleta_competicao_sexo_dupla (atleta_id, competicao_id, sexo_dupla)
            VALUES $values
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
    }

    public function incluirAtletaCompeticao(AtletaCompeticao $atletaCompeticao) : bool
    {
        $idAtleta     = $atletaCompeticao->atleta()->id();
        $idCompeticao = $atletaCompeticao->competicao()->id();

        $sql = <<<SQL
            INSERT INTO atleta_competicao (atleta_id, competicao_id, informacoes)
            VALUES (:atleta_id, :competicao_id, :informacoes)
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'atleta_id'     => $idAtleta,
            'competicao_id' => $idCompeticao,
            'informacoes'   => $atletaCompeticao->informacao()
        ]);

        $this->salvarCategorias($idAtleta, $idCompeticao, $atletaCompeticao->categorias());

        $this->salvarSexoDupla($idAtleta, $idCompeticao, $atletaCompeticao->sexoDupla());

        return true;
    }

    public function get(Atleta $atleta, Competicao $competicao): ?AtletaCompeticao
    {
        $sql = <<<SQL
                  SELECT ac.informacoes
                       , ac.criado_em
                       , ac.alterado_em
                       , jsonb_agg(acs.sexo_dupla) as sexo_dupla
                       , jsonb_agg(jsonb_build_object(
                            'id', cat.id,
                            'descricao', cat.descricao,
                            'idade_maior_que', cat.idade_maior_que,
                            'idade_menor_que', cat.idade_menor_que
                       )) as categorias
                    FROM atleta_competicao ac
            NATURAL JOIN atleta_competicao_categoria acc
            NATURAL JOIN atleta_competicao_sexo_dupla acs
                    JOIN categoria cat
                      ON cat.id = acc.categoria_id
                   WHERE (ac.atleta_id, ac.competicao_id) = (:atleta_id, :competicao_id)
                GROUP BY ac.atleta_id, ac.competicao_id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'atleta_id'     => $atleta->id(),
            'competicao_id' => $competicao->id(),
        ]);

        $rows = $stmt->fetchAll();
        if (count($rows) != 1) {
            return null;
        }
        $row = $rows[0];

        $sexoDupla = array_map(
            fn(string $s): Sexo => Sexo::from($s),
            json_decode($row['sexo_dupla'], true),
        );

        $categorias = [];
        foreach (json_decode($row['categorias'], true) as $arr) {
            $categorias[] = new Categoria(
                (int) $arr['id'],
                $arr['descricao'],
                is_null($arr['idade_maior_que']) ? null : (int) $arr['idade_maior_que'],
                is_null($arr['idade_menor_que']) ? null : (int) $arr['idade_menor_que'],
            );
        }

        return (new AtletaCompeticao)
            ->setAtleta($atleta)
            ->setCompeticao($competicao)
            ->setInformacao($row['informacoes'])
            ->addCategoria(...$categorias)
            ->addSexoDupla(...$sexoDupla)
            ->setDataAlteracao(Dates::parseMicro($row['alterado_em']))
            ->setDataCriacao(Dates::parseMicro($row['criado_em']))
            ;
    }

    public function getViaId(int $idAtleta, int $idCompeticao): ?array
    {
        // TODO por enquanto traz somente as informações que são usadas para
        // pré-preencher os filtros, mas também poderia trazer em cima os
        // dados do atleta para visualizar qual atleta está sendo considerado
        // (no mínimo o nome -- "Mostrando atletas compatíveis com $nome")

        $sql = <<<SQL
              SELECT a.sexo,
                     jsonb_agg(distinct acc.categoria_id) as categorias,
                     jsonb_agg(distinct acs.sexo_dupla) as sexo_duplas
                FROM atleta a
                JOIN atleta_competicao ac
                  ON ac.atleta_id = :atleta_id
                 AND ac.competicao_id = :competicao_id
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
            'atleta_id'     => $idAtleta,
            'competicao_id' => $idCompeticao,
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

    public function getViaTecnico(int $idTecnico, int $idCompeticao): array
    {
        $sql = <<<SQL
            SELECT a.nome_completo,
                   a.sexo,
                   a.data_nascimento,
                   a.path_foto,
                   a.informacoes,
                   a.id,
                   a.criado_em,
                   a.alterado_em,
                   ac.informacoes AS informacoes_atleta_competicao,
                   jsonb_agg(DISTINCT cat.descricao) AS categorias,
                   jsonb_agg(DISTINCT acsd.sexo_dupla) AS sexo_duplas
              FROM atleta a
              JOIN atleta_competicao ac
                ON ac.atleta_id = a.id
               AND ac.competicao_id = :competicao_id
              JOIN tecnico t
                ON a.tecnico_id = t.id
               AND t.id = :tecnico_id
              JOIN atleta_competicao_categoria acc
                ON a.id = acc.atleta_id
               AND acc.competicao_id = ac.competicao_id
              JOIN categoria cat
                ON cat.id = acc.categoria_id
              JOIN atleta_competicao_sexo_dupla acsd
                ON a.id = acsd.atleta_id
               AND acsd.competicao_id = ac.competicao_id
          GROUP BY a.id, ac.informacoes
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'tecnico_id'    => $idTecnico,
            'competicao_id' => $idCompeticao,
        ]);
        $rows = $stmt->fetchAll();

        $atletas = [];
        foreach ($rows as $row) {
            $atletas[] = [
                ...$row,
                'sexo' => Sexo::from($row['sexo'])->toString(),
                'sexo_duplas' => array_map(fn($s) => Sexo::from($s)->value, json_decode($row['sexo_duplas'])),
                'categorias'  => json_decode($row['categorias']),
                'idade' => Dates::age(Dates::parseDay($row['data_nascimento'])),
                'data_nascimento' => Dates::parseDay($row['data_nascimento'])->format('d/m/Y'),
                'criado_em' => Dates::parseMicro($row['criado_em'])->format('d/m/Y H:i:s'),
                'alterado_em' => Dates::parseMicro($row['alterado_em'])->format('d/m/Y H:i:s'),
            ];
        }

        return $atletas;
    }
}
