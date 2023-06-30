<?php

namespace App\Categorias;

use DateTimeInterface;

readonly class Categoria
{
    public function __construct(
        private ?int $id = null,
        private ?string $descricao = null,
        private ?int $idadeMaiorQue = null,
        private ?int $idadeMenorQue = null,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function descricao(): string
    {
        return $this->descricao;
    }

    public function idadeMaiorQue(): ?int
    {
        return $this->idadeMaiorQue;
    }

    public function idadeMenorQue(): ?int
    {
        return $this->idadeMenorQue;
    }

    public function toJson(): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'idadeMaiorQue' => $this->idadeMaiorQue,
            'idadeMenorQue' => $this->idadeMenorQue,
        ];
    }

    public function podeParticipar(
        DateTimeInterface $dataNascimento,
        DateTimeInterface $dataCompeticao,
    ): bool
    {
        $anoCompeticao = date('Y', $dataCompeticao->getTimestamp());
        $anoNascimento = date('Y', $dataNascimento->getTimestamp());

        // Idade que o atleta vai ter após o seu aniversário no ano da competição
        $idade = $anoCompeticao - $anoNascimento;

        return ($this->idadeMaiorQue == null || $idade > $this->idadeMaiorQue) &&
               ($this->idadeMenorQue == null || $idade < $this->idadeMenorQue);
    }
}
