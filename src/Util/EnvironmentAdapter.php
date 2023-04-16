<?php

namespace App\Util;

use Dotenv\Dotenv;

class EnvironmentAdapter implements EnvironmentAdapterInterface
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
