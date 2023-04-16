<?php

namespace App\Util;

interface EnvironmentInterface
{
    public function get(string $name);
}
