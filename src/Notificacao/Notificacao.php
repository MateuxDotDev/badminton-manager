<?php

namespace App\Notificacao;
use DateTimeInterface;

readonly class Notificacao
{
    private function __construct(
        public TipoNotificacao $tipo,
        public int $idTecnico,
        public ?int $id1 = null,
        public ?int $id2 = null,
        public ?int $id3 = null,
        public ?int $id = null,
        public ?DateTimeInterface $dataCriacao = null,
        public ?DateTimeInterface $dataVisualizacao = null,
    ) {}

    public static function solicitacaoEnviada(
        int $idTecnico,
        int $idSolicitacao,
    ): self
    {
        return new self(
            tipo: TipoNotificacao::SOLICITACAO_ENVIADA,
            idTecnico: $idTecnico,
            id1: $idSolicitacao,
        );
    }

    public static function solicitacaoRecebida(
        int $idTecnico,
        int $idSolicitacao,
    ): self
    {
        return new self(
            tipo: TipoNotificacao::SOLICITACAO_RECEBIDA,
            idTecnico: $idTecnico,
            id1: $idSolicitacao,
        );
    }
}