<?php

namespace App\Util;

use Dotenv\Dotenv;

class Environment
{
    private static bool $initialized = false;

    private static function initialize(): void
    {
        if (!self::$initialized) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../..'); // Ajuste o caminho de acordo com a estrutura do seu projeto
            $dotenv->load();
            self::$initialized = true;
        }
    }

    public static function getPostgresUser(): string
    {
        self::initialize();
        return getenv('POSTGRES_USER');
    }

    public static function getPostgresPassword(): string
    {
        self::initialize();
        return getenv('POSTGRES_PASSWORD');
    }

    public static function getPostgresDb(): string
    {
        self::initialize();
        return getenv('POSTGRES_DB');
    }

    public static function getPostgresHost(): string
    {
        self::initialize();
        return getenv('POSTGRES_HOST');
    }

    public static function getPostgresPort(): int
    {
        self::initialize();
        return (int) getenv('POSTGRES_PORT');
    }
}
