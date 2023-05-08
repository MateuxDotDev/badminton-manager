<?php

namespace App\Util\Traits;

use DateTimeInterface;

trait TemDataAlteracao
{
    private ?DateTimeInterface $dataAlteracao = null;

    public function setDataAlteracao(?DateTimeInterface $dataAlteracao): self
    {
        $this->dataAlteracao = $dataAlteracao;
        return $this;
    }

    public function dataAlteracao(): DateTimeInterface
    {
        return $this->dataAlteracao;
    }
}
