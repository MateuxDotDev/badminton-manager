<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Categorias\CategoriaRepository;
use App\Util\Database\Connection;

try {
    $pdo = Connection::getInstance();
    $repo = new CategoriaRepository($pdo);
    $categorias = $repo->buscarCategorias();

    $array = array_map(fn($c) => $c->toJson(), $categorias);
    echo json_encode($array);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode('Erro ao buscar as categorias');
}