<?php

namespace Tests\Categorias;

use App\Categorias\Categoria;
use App\Categorias\CategoriaRepository;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class CategoriaRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PDOStatement $stmt;
    private CategoriaRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->repository = new CategoriaRepository($this->pdo);
    }

    public function testBuscarCategorias(): void
    {
        $categoriasEsperadas = [
            new Categoria(1, 'Categoria 1', 15, 20),
            new Categoria(2, 'Categoria 2', 20, 30)
        ];

        $this->pdo
            ->expects($this->once())
            ->method('query')
            ->willReturn($this->stmt);

        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['id' => 1, 'descricao' => 'Categoria 1', 'idade_maior_que' => 15, 'idade_menor_que' => 20],
                ['id' => 2, 'descricao' => 'Categoria 2', 'idade_maior_que' => 20, 'idade_menor_que' => 30]
            ]);

        $categorias = $this->repository->buscarCategorias();

        $this->assertEquals($categoriasEsperadas, $categorias);
    }

    public function testGetCategoriaById(): void
    {
        $categoriaEsperada = new Categoria(1, 'Categoria 1', 15, 20);

        $this->pdo
            ->expects($this->once())
            ->method('query')
            ->willReturn($this->stmt);

        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['id' => 1, 'descricao' => 'Categoria 1', 'idade_maior_que' => 15, 'idade_menor_que' => 20]
            ]);

        $categoria = $this->repository->getCategoriaById(1);

        $this->assertEquals($categoriaEsperada, $categoria);
    }
}
