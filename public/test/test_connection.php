<?php

require_once 'db_config.php';

try {
    $dsn = "pgsql:host={$db_host};port={$db_port};dbname={$db_name};user={$db_user};password={$db_password}";
    $conn = new PDO($dsn);

    if ($conn) {
        echo "Conexão com o banco de dados bem-sucedida!";
    }
} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage();
}