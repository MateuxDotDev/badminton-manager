<?php

namespace Tests\Tecnico\Atleta\AtletaCompeticao;

use App\Categorias\Categoria;
use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoCategoria;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoCategoriaRepository;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoCategoriaRepositoryInterface;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoRepository;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class AtletaCompeticaoCategoriaRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PDOStatement $stmt;
    private AtletaCompeticaoCategoriaRepositoryInterface $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->repository = new AtletaCompeticaoCategoriaRepository($this->pdo);
    }

    public function testCadastrarAtletaCompeticaoCategoria()
    {
        $executePayload = [
            'atleta_id' => 1,
            'competicao_id' => 1,
            'categoria_id' => 1
        ];

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt->expects($this->once())
            ->method('execute')
            ->with($executePayload)
            ->willReturn(true);

        $atletaCompeticaoCategoria = (new AtletaCompeticaoCategoria())
            ->setAtletaCompeticao(
                (new AtletaCompeticao())
                    ->setAtleta(
                        (new Atleta())
                            ->setId(1)
                    )
                    ->setCompeticao(
                        (new Competicao())
                            ->setId(1)
                    )
            )
            ->setCategoria(
                new Categoria(1, 'Test Category', 10, 20)
            );

        $result = $this->repository->cadastrarAtletaCompeticaoCategoria($atletaCompeticaoCategoria);
        $this->assertTrue($result);
    }
}
