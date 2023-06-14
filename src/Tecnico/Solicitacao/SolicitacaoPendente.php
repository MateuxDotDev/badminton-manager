<?php

namespace App\Tecnico\Solicitacao;

use DateTimeInterface;

readonly class SolicitacaoPendente
{
    public function __construct(
        public int $id,
        public DateTimeInterface $dataCriacao,
        public DateTimeInterface $dataAlteracao,
        public int $idCompeticao,
        public int $idAtletaRemetente,
        public int $idAtletaDestinatario,
        public int $idCategoria,
        public string $informacoes,
    ) {}
}
