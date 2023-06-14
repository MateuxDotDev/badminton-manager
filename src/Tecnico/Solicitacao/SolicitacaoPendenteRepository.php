<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Solicitacao;

use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
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
    public function getEnvolvendo(
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

        return new SolicitacaoPendente(
            (int) $row['id'],
            Dates::parseMicro($row['criado_em']),
            Dates::parseMicro($row['alterado_em']),
            (int) $row['competicao_id'],
            (int) $row['atleta_origem_id'],
            (int) $row['atleta_destino_id'],
            (int) $row['categoria_id'],
            $row['informacoes'],
        );
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
}
