<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Util\Environment\Environment;

function initPdo(): PDO
{
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
