<?php

try {
    $config = require '../db_config.php';
    $conn = new PDO($config->dsn());

    if ($conn) {
        echo "Conexão com o banco de dados bem-sucedida!";
    }
} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage();
}