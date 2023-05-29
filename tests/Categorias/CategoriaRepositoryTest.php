<?php

namespace Tests\Unit\App\Categorias;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use App\Categorias\CategoriaRepository;
use PDO;
use PDOStatement;

class CategoriaRepositoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testBuscarCategorias(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => 1,
                    'descricao' => 'Categoria Teste',
                    'idade_maior_que' => 10,
                    'idade_menor_que' => 20,
                ],
            ]);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects($this->once())
            ->method('query')
            ->with($this->isType('string'))
            ->willReturn($stmt);

        $repository = new CategoriaRepository($pdo);
        $categorias = $repository->buscarCategorias();

        $this->assertCount(1, $categorias);

        $this->assertSame(1, $categorias[0]->id());
        $this->assertSame('Categoria Teste', $categorias[0]->descricao());
        $this->assertSame(10, $categorias[0]->idadeMaiorQue());
        $this->assertSame(20, $categorias[0]->idadeMenorQue());
    }
}
