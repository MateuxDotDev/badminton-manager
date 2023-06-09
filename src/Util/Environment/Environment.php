<?php

namespace App\Util\Environment;

use Dotenv\Dotenv;

class Environment
{
    private static bool $envLoaded = false;

    private static function loadEnv(): void
    {
        if (!self::$envLoaded) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
            $dotenv->load();
            self::$envLoaded = true;
        }
    }

    public static function getPostgresUser(): string
    {
        self::loadEnv();
        return $_ENV['POSTGRES_USER'];
    }

    public static function getPostgresPassword(): string
    {
        self::loadEnv();
        return $_ENV['POSTGRES_PASSWORD'];
    }

    public static function getPostgresDb(): string
    {
        self::loadEnv();
        return $_ENV['POSTGRES_DB'];
    }

    public static function getPostgresHost(): string
    {
        self::loadEnv();
        return $_ENV['POSTGRES_HOST'];
    }

    public static function getPostgresPort(): int
    {
        self::loadEnv();
        return (int) $_ENV['POSTGRES_PORT'];
    }

    public static function getMailUsername(): string
    {
        self::loadEnv();
        return $_ENV['MAIL_USERNAME'];
    }

    public static function getMailPassword(): string
    {
        self::loadEnv();
        return $_ENV['MAIL_PASSWORD'];
    }

    public static function getJwtSecret(): string
    {
        self::loadEnv();
        return $_ENV['JWT_SECRET'];
    }

    public static function getBaseUrl(): string
    {
        self::loadEnv();
        return $_ENV['BASE_URL'];
    }
}
