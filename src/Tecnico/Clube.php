<?php

namespace App\Tecnico;

use DateTimeImmutable;
use DateTimeInterface;

class Clube
{

    private ?int $id = null;
    private string $nome;
    private ?DateTimeInterface $dataCriacao = null;

    public function setId(?int $id): Clube
    {
        $this->id = $id;
        return $this;
    }

    public function setNome(string $nome): Clube
    {
        $this->nome = $nome;
        return $this;
    }

    public function setDataCriacao(?DateTimeInterface $data): Clube
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

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'dataCriacao' => $this->dataCriacao?->format('Y-m-d H:i:s.u'),
        ];
    }

    public function __unserialize(array $a): void
    {
        $id          = ($a['id']          === null) ? null : (int)$a['id'];
        $dataCriacao = ($a['dataCriacao'] === null) ? null : DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $a['dataCriacao']);
        $nome        = $a['nome'];

        $this
            ->setId($id)
            ->setNome($nome)
            ->setDataCriacao($dataCriacao);
    }
}