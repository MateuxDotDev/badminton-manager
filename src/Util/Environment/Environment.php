<?php

namespace App\Util\Environment;

class Environment
{
    private static ?EnvironmentAdapterInterface $environmentAdapter = null;

    public static function setEnvironmentAdapter(?EnvironmentAdapterInterface $adapter): void
    {
        self::$environmentAdapter = $adapter;
    }

    public static function getEnvironmentAdapter(): EnvironmentAdapterInterface
    {
        if (self::$environmentAdapter === null) {
            self::$environmentAdapter = new EnvironmentAdapter();
        }

        return self::$environmentAdapter;
    }

    public static function getPostgresUser(): string
    {
        return self::getEnvironmentAdapter()->get('POSTGRES_USER');
    }

    public static function getPostgresPassword(): string
    {
        return self::getEnvironmentAdapter()->get('POSTGRES_PASSWORD');
    }

    public static function getPostgresDb(): string
    {
        return self::getEnvironmentAdapter()->get('POSTGRES_DB');
    }

    public static function getPostgresHost(): string
    {
        return self::getEnvironmentAdapter()->get('POSTGRES_HOST');
    }

    public static function getPostgresPort(): int
    {
        return (int) self::getEnvironmentAdapter()->get('POSTGRES_PORT');
    }
}
