<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Atleta;

use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\Http\HttpStatus;
use App\Util\Services\UploadImagemService\UploadImagemServiceInterface;
use Exception;
use PDO;

class AtletaRepository implements AtletaRepositoryInterface
{

    private bool $defineTransaction;

    public function __construct(
        private readonly PDO $pdo,
        private readonly UploadImagemServiceInterface $uploadImagemService
    ) {
        $this->defineTransaction = true;
    }

    /**
     * @throws Exception
     */
    public function criarAtleta(Atleta $atleta): int
    {
        $this->begin();
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
            $this->commit();

            return $atleta->id();
        } catch (Exception $e) {
            $this->uploadImagemService->removerImagem($atleta->foto());
            $this->rollback();
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

    public function getAtletaViaId(int $id): ?Atleta
    {
        $sql = <<<SQL
            SELECT id, nome_completo, sexo, data_nascimento, informacoes, path_foto, criado_em, alterado_em
              FROM atleta
             WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll();

        $atleta = null;
        foreach ($rows as $row) {
            $atleta = (new Atleta())
                ->setId($row['id'])
                ->setNomeCompleto($row['nome_completo'])
                ->setSexo(Sexo::from($row['sexo']))
                ->setDataNascimento(Dates::parseDay($row['data_nascimento']))
                ->setInformacoesAdicionais($row['informacoes'])
                ->setDataCriacao(Dates::parseMicro($row['criado_em']))
                ->setDataAlteracao(Dates::parseMicro($row['alterado_em']))
                ->setFoto($row['path_foto']);
        }

        return $atleta;
    }

    public function defineTransaction(bool $define){
        $this->defineTransaction = $define;
    }

    private function begin(){
        if($this->defineTransaction){
            $this->pdo->beginTransaction();
        }
    }

    private function commit(){
        if($this->defineTransaction){
            $this->pdo->commit();
        }
    }

    private function rollback(){
        if($this->defineTransaction){
            $this->pdo->rollback();
        }
    }
}
