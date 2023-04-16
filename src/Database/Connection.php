<?php

namespace App\Database;

use App\Util\Environment;
use PDO;

class Connection implements ConnectionInterface
{
    private static ?PDO $instance = null;

    public function __construct()
    {
        // This class is a singleton, so it should not be instantiated.
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                Environment::getPostgresHost(),
                Environment::getPostgresPort(),
                Environment::getPostgresDb()
            );

            $pdo = new PDO(
                $dsn,
                Environment::getPostgresUser(),
                Environment::getPostgresPassword(),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]            
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("set time zone -3");

            return $pdo;
        }

        return self::$instance;
    }

    public function setInstance(PDO $instance): void
    {
        self::$instance = $instance;
    }
}
