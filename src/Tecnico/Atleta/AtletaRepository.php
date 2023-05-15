<?php

namespace App\Tecnico\Atleta;

use App\Util\Exceptions\ValidatorException;
use App\Util\Http\HttpStatus;
use Exception;
use PDO;

class AtletaRepository implements AtletaRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
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
                SELECT id
                  FROM atleta
                 WHERE nome_completo = :nome_completo
                   AND id_tecnico = :tecnico_id
            SQL;

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nome_completo' => $atleta->nomeCompleto(),
                'tecnico_id' => $atleta->tecnico()->id()
            ]);
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                throw new ValidatorException(
                    "Atleta '{$atleta->nomeCompleto()}' já existe",
                    HttpStatus::CONFLICT
                );
            }

            $sql = <<<SQL
                INSERT INTO atleta (
                    id,
                    id_tecnico,
                    nome_completo,
                    sexo,
                    data_nascimento,
                    informacoes_adicionais,
                    caminho_foto
                )
                VALUES (
                    :id,
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
                'id' => $atleta->id(),
                'id_tecnico' => $atleta->tecnico()->id(),
                'nome_completo' => $atleta->nomeCompleto(),
                'sexo' => $atleta->sexo()->value,
                'data_nascimento' => $atleta->dataNascimento()->format('Y-m-d'),
                'informacoes_adicionais' => $atleta->informacoesAdicionais(),
                'caminho_foto' => $atleta->foto()()
            ]);

            $atleta->setId($pdo->lastInsertId());
            $pdo->commit();

            return $atleta->id();
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }
}
