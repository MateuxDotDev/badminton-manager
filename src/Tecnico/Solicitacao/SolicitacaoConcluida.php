<?php

namespace App\Tecnico\Solicitacao;

use App\Util\General\Dates;
use DateTimeInterface;

readonly class SolicitacaoConcluida
{
    public function __construct(
        private int $id,
        private int $competicaoId,
        private int $atletaOrigemId,
        private int $atletaDestinoId,
        private string $informacoes,
        private int $categoriaId,
        private DateTimeInterface $criadoEm,
        private DateTimeInterface $alteradoEm,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function competicaoId(): int
    {
        return $this->competicaoId;
    }

    public function atletaOrigemId(): int
    {
        return $this->atletaOrigemId;
    }

    public function atletaDestinoId(): int
    {
        return $this->atletaDestinoId;
    }

    public function informacoes(): string
    {
        return $this->informacoes;
    }

    public function categoriaId(): int
    {
        return $this->categoriaId;
    }

    public function criadoEm(): DateTimeInterface
    {
        return $this->criadoEm;
    }

    public function alteradoEm(): DateTimeInterface
    {
        return $this->alteradoEm;
    }


    public static function fromRow(array $row): SolicitacaoConcluida
    {
        return new self(
            $row['id'],
            $row['competicao_id'],
            $row['atleta_origem_id'],
            $row['atleta_destino_id'],
            $row['informacoes'],
            $row['categoria_id'],
            Dates::parseMicro($row['criado_em']),
            Dates::parseMicro($row['alterado_em']),
        );
    }
}
