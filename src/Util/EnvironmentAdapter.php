<?php

namespace App\Util;

class EnvironmentAdapter implements EnvironmentInterface
{
    public function get(string $name)
    {
        return getenv($name);
    }
}
