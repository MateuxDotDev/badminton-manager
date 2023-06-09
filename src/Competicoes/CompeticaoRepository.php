<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Competicoes;

use App\Util\General\Dates;
use PDO;

class CompeticaoRepository implements CompeticaoRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    public function todasAsCompeticoes(): array
    {
        $sql = <<<SQL
            SELECT id,
                   nome,
                   prazo,
                   descricao,
                   criado_em,
                   alterado_em
              FROM competicao
          ORDER BY prazo ASC
        SQL;

        $query = $this->pdo->query($sql);
        $competicoes = [];
        foreach ($query as $linha) {
            $competicoes[] = (new Competicao)
                ->setId((int) $linha['id'])
                ->setNome($linha['nome'])
                ->setDescricao($linha['descricao'])
                ->setPrazo(Dates::parseDay($linha['prazo']))
                ->setDataAlteracao(Dates::parseMicro($linha['alterado_em']))
                ->setDataCriacao(Dates::parseMicro($linha['criado_em']));
        }
        return $competicoes;
    }

    public function competicoesAbertas(): array
    {
        $qry = $this->pdo->query(<<<SQL
            SELECT id,
                   nome,
                   prazo,
                   descricao
              FROM competicao
             WHERE prazo >= NOW()
          ORDER BY prazo ASC
        SQL);
        $competicoes = [];
        foreach ($qry as $linha) {
            $competicoes[] = (new Competicao)
                ->setId((int) $linha['id'])
                ->setNome($linha['nome'])
                ->setPrazo(Dates::parseDay($linha['prazo']))
                ->setDescricao($linha['descricao']);
        }
        return $competicoes;
    }

    public function criarCompeticao(Competicao $competicao): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO competicao (nome, prazo, descricao)
                 VALUES (:nome, :prazo, :descricao)
        ");
        $stmt->execute([
            'nome' => $competicao->nome(),
            'prazo' => $competicao->prazo()->format('Y-m-d'),
            'descricao' => $competicao->descricao()
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
                 descricao = :descricao,
                 alterado_em = NOW()
           WHERE id = :id
        ");
        $stmt->execute([
            'id' => $competicao->id(),
            'nome' => $competicao->nome(),
            'prazo' => $competicao->prazo()->format('Y-m-d'),
            'descricao' => $competicao->descricao()
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

    public function getViaId(int $id): ?Competicao
    {
        $sql = <<<SQL
            SELECT id,
                   nome,
                   descricao,
                   prazo,
                   criado_em,
                   alterado_em
              FROM competicao
             WHERE id = :id
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $rows = $stmt->fetchAll();
        if (empty($rows)) {
            return null;
        }
        return Competicao::fromRow($rows[0]);
    }


    public function getViaIds(array $idCompeticoes): array
    {
        if (empty($idCompeticoes)) {
            return [];
        }

        $condicao = 'id IN ('.implode(',', array_fill(0, count($idCompeticoes), '?')).')';
        $parametros = $idCompeticoes;

        $sql = <<<SQL
            SELECT id,
                   nome,
                   descricao,
                   prazo,
                   criado_em,
                   alterado_em
              FROM competicao
             WHERE $condicao
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        $retorno = [];
        while ($row = $stmt->fetch()) {
            $retorno[] = Competicao::fromRow($row);
        }
        return $retorno;
    }
}
