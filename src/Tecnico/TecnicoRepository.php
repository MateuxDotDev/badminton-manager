<?php

namespace App\Tecnico;

use App\SenhaCriptografada;
use App\Util\Dates;
use \PDO;
use \Exception;

readonly class TecnicoRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    /**
     * @throws Exception
     */
    private function getViaChave(string $chave, string $valor): ?Tecnico
    {
        if ($chave != 'email' && $chave != 'id') {
            throw new Exception("Chave de técnico '$chave' inválida");
        }

        $sql = <<<SQL
            SELECT t.id,
                   t.email,
                   t.nome_completo,
                   t.informacoes,
                   t.hash_senha,
                   t.salt_senha,
                   c.id as clube_id,
                   c.nome as clube_nome,
                   c.criado_em as clube_criado_em,
                   t.criado_em,
                   t.alterado_em
              FROM tecnico t
              JOIN clube c
                ON c.id = t.clube_id
        SQL;

        if ($chave == 'email') {
            $sql .= ' WHERE email = :email';
            $params = ['email' => $valor];
        } else {
            $sql .= ' WHERE id = :id';
            $params = ['id' => $valor];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll();
        if (count($rows) != 1) {
            return null;
        }

        $row = $rows[0];

        $dataCriacao   = Dates::parseMicro($row['criado_em']);
        $dataAlteracao = Dates::parseMicro($row['alterado_em']);

        $clube = (new Clube)
            ->setId((int) $row['clube_id'])
            ->setNome($row['clube_nome'])
            ->setDataCriacao(Dates::parseMicro($row['clube_criado_em']))
            ;

        $senha = SenhaCriptografada::existente($row['hash_senha'], $row['salt_senha']);

        return (new Tecnico)
            ->setId((int) $row['id'])
            ->setEmail($row['email'])
            ->setSenhaCriptografada($senha)
            ->setNomeCompleto($row['nome_completo'])
            ->setInformacoes($row['informacoes'])
            ->setDataCriacao($dataCriacao)
            ->setDataAlteracao($dataAlteracao)
            ->setClube($clube)
            ;
    }

    /**
     * @throws Exception
     */
    public function getViaEmail(string $email): ?Tecnico
    {
        return $this->getViaChave('email', $email);
    }

    /**
     * @throws Exception
     */
    public function getViaId(int $id): ?Tecnico
    {
        return $this->getViaChave('id', (string) $id);
    }

    /**
     * @throws Exception
     */
    public function criarTecnico(Tecnico $tecnico): void
    {
        $pdo = $this->pdo;

        $pdo->beginTransaction();
        try {
            $clube = $tecnico->clube();
            if ($clube->id() === null) {
                $sql = 'INSERT INTO clube (nome) VALUES (:nome)';
                $pdo->prepare($sql)->execute(['nome' => $clube->nome()]);
                $clube->setId($pdo->lastInsertId());
            }

            $sql = <<<SQL
                INSERT INTO tecnico (email, nome_completo, informacoes, clube_id, hash_senha, salt_senha)
                VALUES (:email, :nomeCompleto, :informacoes, :idClube, :hashSenha, :saltSenha)
            SQL;
            $pdo->prepare($sql)->execute([
                'email' => $tecnico->email(),
                'nomeCompleto' => $tecnico->nomeCompleto(),
                'informacoes' => $tecnico->informacoes(),
                'idClube' => $tecnico->clube()->id(),
                'hashSenha' => $tecnico->senhaCriptografada()?->hash(),
                'saltSenha' => $tecnico->senhaCriptografada()?->salt(),
            ]);

            $tecnico->setId($pdo->lastInsertId());
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }
}
