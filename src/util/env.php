<?php

function _getenv($env): string|array
{
    $variable = getenv($env);
    if ($variable === false) {
        die("Falta $env no .env");
    }
    return $variable;
}

$GLOBALS['env'] = [
    'postgres' => [
        'host' => _getenv('POSTGRES_HOST'),
        'port' => _getenv('POSTGRES_PORT'),
        'name' => _getenv('POSTGRES_DB'),
        'user' => _getenv('POSTGRES_USER'),
        'password' => _getenv('POSTGRES_PASSWORD'),
    ],
];
