<?php

try {
    $conn = require '../db_connect.php';

    if ($conn) {
        echo "Conexão com o banco de dados bem-sucedida!";
    }
} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage();
}