<?php

namespace App\Util;

use Dotenv\Dotenv;

class Environment
{
    private static bool $initialized = false;
    private static ?EnvironmentInterface $environmentAdapter = null;

    private static function initialize(): void
    {
        if (!self::$initialized) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
            $dotenv->load();
            self::$initialized = true;
        }
    }

    public static function setEnvironmentAdapter(EnvironmentInterface $adapter): void
    {
        self::$environmentAdapter = $adapter;
    }

    public static function getEnvironmentAdapter(): EnvironmentInterface
    {
        if (self::$environmentAdapter === null) {
            self::$environmentAdapter = new EnvironmentAdapter();
        }

        return self::$environmentAdapter;
    }

    public static function getPostgresUser(): string
    {
        self::initialize();
        return self::getEnvironmentAdapter()->get('POSTGRES_USER');
    }

    public static function getPostgresPassword(): string
    {
        self::initialize();
        return self::getEnvironmentAdapter()->get('POSTGRES_PASSWORD');
    }

    public static function getPostgresDb(): string
    {
        self::initialize();
        return self::getEnvironmentAdapter()->get('POSTGRES_DB');
    }

    public static function getPostgresHost(): string
    {
        self::initialize();
        return self::getEnvironmentAdapter()->get('POSTGRES_HOST');
    }

    public static function getPostgresPort(): int
    {
        self::initialize();
        return (int) self::getEnvironmentAdapter()->get('POSTGRES_PORT');
    }
}
