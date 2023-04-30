<?php

namespace App\Tecnico;

use DateTimeInterface;

class Clube {

    private ?int $id = null;
    private string $nome;
    private ?DateTimeInterface $dataCriacao = null;

    public function setId(int $id): Clube
    {
        $this->id = $id;
        return $this;
    }

    public function setNome(string $nome): Clube
    {
        $this->nome = $nome;
        return $this;
    }

    public function setDataCriacao(DateTimeInterface $data): Clube
    {
        $this->dataCriacao = $data;
        return $this;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function nome(): string
    {
        return $this->nome;
    }

    public function dataCriacao(): ?DateTimeInterface
    {
        return $this->dataCriacao;
    }
}