<?php

namespace App\Util\Environment;

use Dotenv\Dotenv;

class Environment
{
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->safeLoad();
    }

    public static function getPostgresUser(): string
    {
        return getenv('POSTGRES_USER');
    }

    public static function getPostgresPassword(): string
    {
        return getenv('POSTGRES_PASSWORD');
    }

    public static function getPostgresDb(): string
    {
        return getenv('POSTGRES_DB');
    }

    public static function getPostgresHost(): string
    {
        return getenv('POSTGRES_HOST');
    }

    public static function getPostgresPort(): int
    {
        return (int)getenv('POSTGRES_PORT');
    }
}
