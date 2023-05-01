<?php

namespace App\Admin\Competicoes;

use DateTimeImmutable;
use PDO;

readonly class CompeticaoRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function todasAsCompeticoes(): array
    {
        $qry = $this->pdo->query("
            SELECT id,
                   nome,
                   prazo,
                   criado_em,
                   alterado_em
              FROM competicao
          ORDER BY prazo DESC
        ");
        $competicoes = [];
        foreach ($qry as $linha) {
            $competicoes[] = (new Competicao)
                ->setId((int) $linha['id'])
                ->setNome($linha['nome'])
                ->setPrazo(DateTimeImmutable::createFromFormat('Y-m-d', $linha['prazo']))
                ->setDataAlteracao(DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $linha['alterado_em']))
                ->setDataCriacao(DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $linha['criado_em']))
                ;
        }
        return $competicoes;
    }

    public function criarCompeticao(Competicao $competicao): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO competicao (nome, prazo)
            VALUES (:nome, :prazo)
        ");
        $stmt->execute([
            'nome' => $competicao->nome(),
            'prazo' => $competicao->prazo()->format('Y-m-d'),
        ]);
        $id = $this->pdo->lastInsertId();
        $competicao->setId($id);
        return $id;
    }

    public function alterarCompeticao(Competicao $competicao): bool
    {
        $stmt = $this->pdo->prepare("
          UPDATE competicao
             SET nome = :nome,
                 prazo = :prazo,
                 alterado_em = NOW()
           WHERE id = :id
        ");
        $stmt->execute([
            'id' => $competicao->id(),
            'nome' => $competicao->nome(),
            'prazo' => $competicao->prazo()->format('Y-m-d'),
        ]);
        return $stmt->rowCount() == 1;
    }

    public function excluirCompeticao(int $id): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM competicao
                  WHERE id = :id
          ");
        $stmt->execute(['id' => $id]);
    }
}
