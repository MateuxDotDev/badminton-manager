<?php

namespace App\Util\Traits;

trait TemId
{
    private ?int $id = null;

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function id(): ?int
    {
        return $this->id;
    }
}
