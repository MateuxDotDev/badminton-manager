<?php

namespace App\Tecnico;

use App\Util\General\Dates;
use App\Util\Traits\TemDataCriacao;
use App\Util\Traits\TemId;

class Clube
{
    use TemDataCriacao, TemId;

    private ?string $nome = null;

    public function setNome(string $nome): Clube
    {
        $this->nome = $nome;
        return $this;
    }

    public function nome(): ?string
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
