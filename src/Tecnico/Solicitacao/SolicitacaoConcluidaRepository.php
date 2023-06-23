<?php

namespace App\Tecnico\Solicitacao;

use App\Util\Exceptions\ValidatorException;
use App\Util\Http\HttpStatus;
use PDO;

class SolicitacaoConcluidaRepository
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    public function excluirPendente(int $id): void
    {
        $sql = <<<SQL
            DELETE FROM solicitacao_dupla_pendente
            WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute(['id' => $id]);
    }

    /**
     * @throws ValidatorException
     */
    private function transferir(int $idPendente, TipoConclusao $tipo, ?int $idSolicitacaoCancelou=null): int
    {
        $colunaData = match ($tipo) {
            TipoConclusao::ACEITA => 'aceita_em',
            TipoConclusao::REJEITADA => 'rejeitada_em',
            TipoConclusao::CANCELADA => 'cancelada_em',
        };

        $sql = <<<SQL
            INSERT INTO solicitacao_dupla_concluida (
                competicao_id,
                atleta_origem_id,
                atleta_destino_id,
                informacoes,
                categoria_id,
                criado_em,
                alterado_em,
                solicitacao_cancelamento_id,
                $colunaData
            )
            SELECT
                competicao_id,
                atleta_origem_id,
                atleta_destino_id,
                informacoes,
                categoria_id,
                criado_em,
                alterado_em,
                :cancelamento_id,
                NOW() as $colunaData
            FROM solicitacao_dupla_pendente
            WHERE id = :id
            RETURNING id
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id'              => $idPendente,
            'cancelamento_id' => $idSolicitacaoCancelou
        ]);

        $rows = $stmt->fetchAll();
        if (empty($rows)) {
            throw new ValidatorException('Sem resultados', HttpStatus::NOT_FOUND);
        }
        return $rows[0]['id'];
    }

    /**
     * @throws ValidatorException
     */
    public function concluirRejeitada(int $idPendente): int
    {
        $idConcluida = $this->transferir($idPendente, TipoConclusao::REJEITADA);
        $this->excluirPendente($idPendente);
        return $idConcluida;
    }

    /**
     * @throws ValidatorException
     */
    public function concluirAceita(int $idPendente): int
    {
        $idConcluida = $this->transferir($idPendente, TipoConclusao::ACEITA);
        $this->excluirPendente($idPendente);
        return $idConcluida;
    }

    /**
     * @throws ValidatorException
     */
    public function concluirCancelada(int $idPendente, ?int $idSolicitacaoCancelou=null): int
    {
        $idConcluida = $this->transferir($idPendente, TipoConclusao::CANCELADA, $idSolicitacaoCancelou);
        $this->excluirPendente($idPendente);
        return $idConcluida;
    }
}
