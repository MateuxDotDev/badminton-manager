<?php /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Categorias\Categoria;
use App\Competicoes\Competicao;
use App\Util\General\Dates;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Atleta\Atleta;
use \PDO;
use \Exception;

class AtletaCompeticaoRepository
{

    private PDO $pdo;
    private bool $defineTransaction;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->defineTransaction = true;
    }

    public function getAtletasForaCompeticao(int $idTecnico, int $idCompeticao): array
    {
        try {
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
                $atletas[] = (new Atleta)
                ->setNomeCompleto($linha['nome_completo'])
                ->setSexo(Sexo::from($linha['sexo']))
                ->setDataNascimento(Dates::parseDay($linha['data_nascimento']))
                ->setInformacoesAdicionais($linha['informacoes'])
                ->setFoto($linha['path_foto'])
                ->setDataCriacao(Dates::parseMicro($linha['criado_em']))
                ->setDataAlteracao(Dates::parseMicro($linha['alterado_em']))
                ->setId($linha['id'])
                ;
            }
            return $atletas;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
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

    public function incluirAtletaCompeticao(AtletaCompeticao $ac) : bool
    {
        $idAtleta     = $ac->atleta()->id();
        $idCompeticao = $ac->competicao()->id();

        $this->begin();
        try {
            $sql = <<<SQL
                INSERT INTO atleta_competicao (atleta_id, competicao_id, informacoes)
                VALUES (:atleta_id, :competicao_id, :informacoes)
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'atleta_id'     => $idAtleta,
                'competicao_id' => $idCompeticao,
                'informacoes'   => $ac->informacao()
            ]);

            $this->salvarCategorias($idAtleta, $idCompeticao, $ac->categorias());

            $this->salvarSexoDupla($idAtleta, $idCompeticao, $ac->sexoDupla());

            $this->commit();

            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
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

    public function defineTransaction(bool $define)
    {
        $this->defineTransaction = $define;
    }

    private function begin()
    {
        if ($this->defineTransaction) {
            $this->pdo->beginTransaction();
        }
    }

    private function commit()
    {
        if ($this->defineTransaction) {
            $this->pdo->commit();
        }
    }

    private function rollback()
    {
        if ($this->defineTransaction) {
            $this->pdo->rollback();
        }
    }
}