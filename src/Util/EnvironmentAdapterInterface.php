<?php

namespace App\Util;

interface EnvironmentAdapterInterface
{
    public function get(string $name);
}
