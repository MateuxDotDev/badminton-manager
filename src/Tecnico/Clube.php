<?php

namespace App\Tecnico;

use App\TemDataCriacao;
use App\Util\Dates;

class Clube
{
    use TemDataCriacao;

    private ?int $id = null;
    private string $nome;

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

    public function id(): ?int
    {
        return $this->id;
    }

    public function nome(): string
    {
        return $this->nome;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'dataCriacao' => Dates::formatMicro($this->dataCriacao)
        ];
    }

    public function __unserialize(array $a): void
    {
        $id = ($a['id'] === null) ? null : (int) $a['id'];
        $nome = $a['nome'];
        $dataCriacao = Dates::parseMicro($a['dataCriacao']);

        $this
            ->setId($id)
            ->setNome($nome)
            ->setDataCriacao($dataCriacao);
    }
}
