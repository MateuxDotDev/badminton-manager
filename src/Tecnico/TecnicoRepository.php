<?php

namespace App\Tecnico;

use \DateTimeImmutable;
use \PDO;
use \Exception;

// TODO testes

class TecnicoRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

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
                c.id as clube_id,
                c.nome as clube_nome,
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

        $dataCriacao   = DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $row['criado_em']);
        $dataAlteracao = DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $row['alterado_em']);

        $clube = (new Clube)
            ->setId((int) $row['clube_id'])
            ->setNome($row['clube_nome'])
            ;

        return (new Tecnico)
            ->setId((int) $row['id'])
            ->setEmail($row['email'])
            ->setNomeCompleto($row['nome_completo'])
            ->setInformacoes($row['informacoes'])
            ->setDataCriacao($dataCriacao)
            ->setDataAlteracao($dataAlteracao)
            ->setClube($clube)
            ;
    }

    public function getViaEmail(string $email): ?Tecnico
    {
        return $this->getViaChave('email', $email);
    }

    public function getViaId(int $id): ?Tecnico
    {
        return $this->getViaChave('id', (string) $id);
    }

    public function criarTecnico(Tecnico $tecnico, string $hashSenha, string $saltSenha): void
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
                'hashSenha' => $hashSenha,
                'saltSenha' => $saltSenha,
            ]);

            $tecnico->setId($pdo->lastInsertId());
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }
}