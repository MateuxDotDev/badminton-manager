<?php

namespace App\Test;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Database\Connection;
use PDOException;

try {
    $conn = Connection::getInstance();

    if ($conn) {
        echo "Conexão com o banco de dados bem-sucedida!";
    }
} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage();
}
