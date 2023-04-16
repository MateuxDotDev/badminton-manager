<?php

namespace App\Util;

class EnvironmentAdapter implements EnvironmentInterface
{
    public function get(string $name): bool|array|string
    {
        return getenv($name);
    }
}
