<?php

namespace App\Util\Environment;

interface EnvironmentAdapterInterface
{
    public function get(string $name);
}
