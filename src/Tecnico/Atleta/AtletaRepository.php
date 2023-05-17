<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Atleta;

use App\Util\Exceptions\ValidatorException;
use App\Util\Http\HttpStatus;
use App\Util\Services\UploadImagemService\UploadImagemServiceInterface;
use Exception;
use PDO;

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
                    id_tecnico,
                    nome_completo,
                    sexo,
                    data_nascimento,
                    informacoes_adicionais,
                    caminho_foto
                )
                VALUES (
                    :id_tecnico,
                    :nome_completo,
                    :sexo,
                    :data_nascimento,
                    :informacoes_adicionais,
                    :caminho_foto
                )
            SQL;

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id_tecnico' => $atleta->tecnico()->id(),
                'nome_completo' => $atleta->nomeCompleto(),
                'sexo' => $atleta->sexo()->value,
                'data_nascimento' => $atleta->dataNascimento()->format('Y-m-d'),
                'informacoes_adicionais' => $atleta->informacoesAdicionais(),
                'caminho_foto' => $atleta->foto()
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
}
