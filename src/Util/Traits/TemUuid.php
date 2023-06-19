<?php

namespace App\Util\Traits;

trait TemUuid
{
    private ?String $uuid = null;

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function id(): ?string
    {
        return $this->uuid;
    }
}
