<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Solicitacao;

use App\Util\Exceptions\ValidatorException;
use Exception;
use PDO;

class SolicitacaoPendenteRepository implements SolicitacaoPendenteRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    /**
     * @throws Exception
     */
    public function getViaIds(
        int $idCompeticao,
        int $idAtleta1,
        int $idAtleta2,
        int $idCategoria
    ): ?SolicitacaoPendente
    {
        $pdo = $this->pdo;

        $sql = <<<SQL
            select id
                 , competicao_id
                 , atleta_origem_id
                 , atleta_destino_id
                 , categoria_id
                 , criado_em
                 , alterado_em
                 , informacoes
              from solicitacao_dupla_pendente
             where atleta_origem_id in (:id1, :id2)
               and atleta_destino_id in (:id1, :id2)
               and categoria_id = :idCategoria
               and competicao_id = :idCompeticao
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id1' => $idAtleta1,
            'id2' => $idAtleta2,
            'idCompeticao' => $idCompeticao,
            'idCategoria' => $idCategoria,
        ]);

        $rows = $stmt->fetchAll();

        if (count($rows) > 1) {
            throw new ValidatorException(
                'Mais de uma solicitação envolvendo os mesmos atleta e a mesma categoria dentro da mesma competição.'
            );
        }
        if (empty($rows)) {
            return null;
        }
        $row = $rows[0];

        return SolicitacaoPendente::fromRow($row);
    }

    public function enviar(EnviarSolicitacaoDTO $solicitacao): int
    {
        $pdo = $this->pdo;

        $sql = <<<SQL
            INSERT INTO solicitacao_dupla_pendente
            (competicao_id, atleta_origem_id, atleta_destino_id, informacoes, categoria_id)
            VALUES
            (:idCompeticao, :idAtletaRemetente, :idAtletaDestinatario, :informacoes, :idCategoria)
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'idCompeticao'         => $solicitacao->idCompeticao,
            'idAtletaRemetente'    => $solicitacao->idAtletaRemetente,
            'idAtletaDestinatario' => $solicitacao->idAtletaDestinatario,
            'idCategoria'          => $solicitacao->idCategoria,
            'informacoes'          => $solicitacao->informacoes,
        ]);

        return $pdo->lastInsertId();
    }

    public function getViaTecnico(int $idTecnico): array
    {
        $sql = <<<SQL
            SELECT s.id
                 , s.competicao_id
                 , s.atleta_origem_id
                 , s.atleta_destino_id
                 , s.informacoes
                 , s.categoria_id
                 , s.criado_em
                 , s.alterado_em
              FROM solicitacao_dupla_pendente s
              JOIN competicao c ON c.id = s.competicao_id
              JOIN atleta ori   ON ori.id  = s.atleta_origem_id
              JOIN atleta dest  ON dest.id = s.atleta_destino_id
             WHERE :tecnico_id IN (ori.tecnico_id, dest.tecnico_id)
               AND NOW() <= c.prazo
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['tecnico_id' => $idTecnico]);

        $retorno = [];
        while ($row = $stmt->fetch()) {
            $retorno[] = SolicitacaoPendente::fromRow($row);
        }
        return $retorno;
    }


    /**
     * @throws Exception
     */
    public function getViaId(int $id): ?SolicitacaoPendente
    {
        $pdo = $this->pdo;

        $sql = <<<SQL
            select id
                 , competicao_id
                 , atleta_origem_id
                 , atleta_destino_id
                 , categoria_id
                 , criado_em
                 , alterado_em
                 , informacoes
              from solicitacao_dupla_pendente
             where id = :id
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
        ]);

        $rows = $stmt->fetchAll();

        if (count($rows) > 1) {
            throw new ValidatorException(
                'Mais de uma solicitação envolvendo os mesmos atleta e a mesma categoria dentro da mesma competição.'
            );
        }

        if (empty($rows)) {
            return null;
        }

        $row = $rows[0];

        return SolicitacaoPendente::fromRow($row);
    }
}
