<?php

namespace App\Tecnico\Solicitacao;

use App\Util\General\Dates;
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

    public static function fromRow(array $row): self
    {
        return new SolicitacaoPendente(
            id: (int) $row['id'],
            dataCriacao: Dates::parseMicro($row['criado_em']),
            dataAlteracao: Dates::parseMicro($row['alterado_em']),
            idCompeticao: (int) $row['competicao_id'],
            idAtletaRemetente: (int) $row['atleta_origem_id'],
            idAtletaDestinatario: (int) $row['atleta_destino_id'],
            idCategoria: (int) $row['categoria_id'],
            informacoes: $row['informacoes']
        );
    }
}
