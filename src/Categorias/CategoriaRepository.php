<?php

namespace App\Categorias;

use \PDO;

class CategoriaRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function buscarCategorias(): array
    {
        $sql = <<<SQL
            SELECT id, descricao, idade_maior_que, idade_menor_que
              FROM categoria
        SQL;

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();

        $categorias = [];
        foreach ($rows as $row) {
            $categorias[] = new Categoria(
                $row['id'],
                $row['descricao'],
                $row['idade_maior_que'],
                $row['idade_menor_que']
            );
        }

        return $categorias;
    }

    public function getCategoriaById(int $id): ?Categoria
    {
        $sql = <<<SQL
            SELECT id, descricao, idade_maior_que, idade_menor_que
              FROM categoria
             WHERE categoria.id = $id
        SQL;

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();

        $categoria = null;
        foreach ($rows as $row) {
            $categoria = new Categoria(
                $row['id'],
                $row['descricao'],
                $row['idade_maior_que'],
                $row['idade_menor_que']
            );
        }

        return $categoria;
    }
}
