<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Atleta;

use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\Http\HttpStatus;
use App\Util\Services\UploadImagemService\UploadImagemServiceInterface;
use \Exception;
use \PDO;

class AtletaRepository implements AtletaRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly UploadImagemServiceInterface $uploadImagemService
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
}
