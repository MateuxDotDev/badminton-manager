<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Atleta;

use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\Http\HttpStatus;
use App\Util\Services\UploadImagemService\UploadImagemService;
use App\Util\Services\UploadImagemService\UploadImagemServiceInterface;
use Exception;
use PDO;

class AtletaRepository implements AtletaRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly UploadImagemServiceInterface $uploadImagemService = new UploadImagemService()
    ) {}

    /**
     * @throws Exception
     */
    public function criarAtleta(Atleta $atleta): int
    {
        $pdo = $this->pdo;

        $pdo->beginTransaction();
        try {
            $sql = <<<SQL
                SELECT id
                  FROM tecnico
                 WHERE id = :id
            SQL;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $atleta->tecnico()->id()]);
            $rows = $stmt->fetchAll();
            if (count($rows) != 1) {
                throw new ValidatorException(
                    "Técnico '{$atleta->tecnico()->nomeCompleto()}' não existe",
                    HttpStatus::NOT_FOUND
                );
            }

            $sql = <<<SQL
                INSERT INTO atleta (
                    tecnico_id,
                    nome_completo,
                    sexo,
                    data_nascimento,
                    informacoes,
                    path_foto
                )
                VALUES (
                    :tecnico_id,
                    :nome_completo,
                    :sexo,
                    :data_nascimento,
                    :informacoes,
                    :path_foto
                )
            SQL;

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'tecnico_id' => $atleta->tecnico()->id(),
                'nome_completo' => $atleta->nomeCompleto(),
                'sexo' => $atleta->sexo()->value,
                'data_nascimento' => $atleta->dataNascimento()->format('Y-m-d'),
                'informacoes' => $atleta->informacoesAdicionais(),
                'path_foto' => $atleta->foto()
            ]);

            $atleta->setId($pdo->lastInsertId());
            $pdo->commit();

            return $atleta->id();
        } catch (Exception $e) {
            $this->uploadImagemService->removerImagem($atleta->foto());
            $pdo->rollback();
            throw $e;
        }
    }

    public function getViaTecnico(int $tecnicoId): array
    {
        $sql = <<<SQL
            SELECT id, nome_completo, sexo, data_nascimento, informacoes, path_foto, criado_em, alterado_em
              FROM atleta
             WHERE tecnico_id = :tecnico_id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['tecnico_id' => $tecnicoId]);
        $rows = $stmt->fetchAll();

        $atletas = [];
        foreach ($rows as $row) {
            $atletas[] = (new Atleta())
                ->setId($row['id'])
                ->setNomeCompleto($row['nome_completo'])
                ->setSexo(Sexo::from($row['sexo']))
                ->setDataNascimento(Dates::parseDay($row['data_nascimento']))
                ->setInformacoesAdicionais($row['informacoes'])
                ->setDataCriacao(Dates::parseMicro($row['criado_em']))
                ->setDataAlteracao(Dates::parseMicro($row['alterado_em']))
                ->setFoto($row['path_foto']);
        }

        return $atletas;
    }

    // TODO na verdade isso aqui deveria estar no AtletaCompeticaoRepository
    // refazer lá depois do merge da branch que criou esse Repository
    // e também pra não inventar um AtletaCompeticao nessa branch que
    // pode divergir do AtletaCompeticao verdadeiro, vou só retornar um json 
    public function getViaIdNaCompeticao(int $idAtleta, int $idCompeticao): ?array
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

        if (empty($rows)) return null;

        $row = $rows[0];

        return [
            'sexo'       => Sexo::from($row['sexo']),
            'categorias' => json_decode($row['categorias']),
            'sexoDuplas' => array_map(fn($s) => Sexo::from($s), json_decode($row['sexo_duplas']))
        ];
    }
}
