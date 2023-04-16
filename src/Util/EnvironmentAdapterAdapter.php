<?php

namespace App\Util;

use Dotenv\Dotenv;

class EnvironmentAdapterAdapter implements EnvironmentAdapterInterface
{
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->safeLoad();
    }

    public function get(string $name): bool|array|string
    {
        return getenv($name);
    }
}
