<?php

namespace App\Test;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Util\Database\Connection;
use Exception;

function tryConnection(): void
{
    try {
        if (Connection::getInstance()) {
            echo "Conexão com o banco de dados bem-sucedida!";
        }
    } catch (Exception $e) {
        echo "Erro na conexão com o banco de dados: " . $e->getMessage();
    }
}

tryConnection();
