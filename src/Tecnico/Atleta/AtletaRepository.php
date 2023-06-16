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
        try {
            $sql = <<<SQL
                SELECT id
                  FROM tecnico
                 WHERE id = :id
            SQL;
            $stmt = $this->pdo->prepare($sql);
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

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'tecnico_id' => $atleta->tecnico()->id(),
                'nome_completo' => $atleta->nomeCompleto(),
                'sexo' => $atleta->sexo()->value,
                'data_nascimento' => $atleta->dataNascimento()->format('Y-m-d'),
                'informacoes' => $atleta->informacoesAdicionais(),
                'path_foto' => $atleta->foto()
            ]);

            $atleta->setId($this->pdo->lastInsertId());

            return $atleta->id();
        } catch (Exception $e) {
            $this->uploadImagemService->removerImagem($atleta->foto());
            throw $e;
        }
    }

    private function get(array $filtros=[]): array
    {
        $condicoes  = [];
        $parametros = [];

        if (array_key_exists('tecnico', $filtros)) {
            $condicoes[]  = 'tecnico_id = ?';
            $parametros[] = (int) $filtros['tecnico'];
        }

        if (array_key_exists('id', $filtros)) {
            $condicoes[]  = 'id = ?';
            $parametros[] = (int) $filtros['id'];
        }

        $where = implode(' AND ', $condicoes);

        $sql = <<<SQL
            SELECT id, nome_completo, sexo, data_nascimento, informacoes, path_foto, criado_em, alterado_em
              FROM atleta
             WHERE $where
          ORDER BY nome_completo
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
        $rows = $stmt->fetchAll();

        $atletas = [];
        foreach ($rows as $row) {
            $atletas[] = (new Atleta())
                ->setId($row['id'])
                ->setNomeCompleto($row['nome_completo'])
                ->setSexo(Sexo::from($row['sexo']))
                ->setDataNascimento(Dates::parseDay($row['data_nascimento']))
                ->setInformacoesAdicionais($row['informacoes'])
                ->setFoto($row['path_foto'])
                ->setDataCriacao(Dates::parseMicro($row['criado_em']))
                ->setDataAlteracao(Dates::parseMicro($row['alterado_em']));
        }

        return $atletas;
    }

    public function removerAtleta(int $atletaId): bool
    {
        $sql = <<<SQL
            DELETE FROM atleta
                  WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $atletaId]);

        return $stmt->rowCount() === 1;
    }

    public function atualizarAtleta(Atleta $atleta): bool
    {
        $sql = <<<SQL
            UPDATE atleta
               SET nome_completo = :nome_completo,
                   sexo = :sexo,
                   data_nascimento = :data_nascimento,
                   informacoes = :informacoes,
                   path_foto = :path_foto,
                   alterado_em = NOW()
             WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nome_completo' => $atleta->nomeCompleto(),
            'sexo' => $atleta->sexo()->value,
            'data_nascimento' => $atleta->dataNascimento()->format('Y-m-d'),
            'informacoes' => $atleta->informacoesAdicionais(),
            'path_foto' => $atleta->foto(),
            'id' => $atleta->id()
        ]);

        return $stmt->rowCount() === 1;
    }

    public function getViaTecnico(int $tecnicoId): array
    {
        return $this->get(['tecnico' => $tecnicoId]);
    }

    public function getViaId(int $idAtleta): ?Atleta
    {
        $atletas = $this->get(['id' => $idAtleta]);
        return empty($atletas) ? null : $atletas[0];
    }
}
