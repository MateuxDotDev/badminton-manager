<?php

namespace App;

use \DateTimeInterface;

trait TemDataCriacao
{
    private ?DateTimeInterface $dataCriacao = null;

    public function setDataCriacao(?DateTimeInterface $dataCriacao): self
    {
        $this->dataCriacao = $dataCriacao;
        return $this;
    }

    public function dataCriacao(): DateTimeInterface
    {
        return $this->dataCriacao;
    }
}