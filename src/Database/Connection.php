<?php

namespace App\Database;

use App\Util\Environment;
use PDO;

class Connection {
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): ?PDO
    {
        if (self::$instance === null) {
            $host = Environment::getPostgresHost();
            $port = Environment::getPostgresPort();
            $name = Environment::getPostgresDb();
            $user = Environment::getPostgresUser();
            $password = Environment::getPostgresPassword();

            $dsn = "pgsql:host=$host;port=$port;dbname=$name;user=$user;password=$password";

            $pdo = new PDO($dsn);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("set time zone -3");

            self::$instance = $pdo;
        }

        return self::$instance;
    }
}
