<?php

namespace App\Tecnico\Solicitacao;

use \PDO;

readonly class SolicitacaoConcluidaRepository
{
    public function __construct(
        private PDO $pdo,
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

    private function transferir(int $idPendente, TipoConclusao $tipo): void
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
                NOW() as $colunaData
            FROM solicitacao_dupla_pendente
           WHERE id = :id
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $idPendente]);
    }

    public function concluirRejeitada(int $idPendente): void
    {
        $this->transferir($idPendente, TipoConclusao::REJEITADA);
        $this->excluirPendente($idPendente);
    }

    public function concluirAceita(int $idPendente): void
    {
        $this->transferir($idPendente, TipoConclusao::ACEITA);
        $this->excluirPendente($idPendente);
    }

    public function concluirCanceladaManualmente(int $idPendente): void
    {
        $this->transferir($idPendente, TipoConclusao::CANCELADA);
        $this->excluirPendente($idPendente);
    }
}