<?php

namespace Tests\Tecnico\Atleta\AtletaCompeticao;

use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoRepository;
use App\Tecnico\Atleta\Sexo;
use ArrayIterator;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class AtletaCompeticaoRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PDOStatement $stmt;
    private AtletaCompeticaoRepository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->repository = new AtletaCompeticaoRepository($this->pdo);
    }

    public function testGetAtletaCompeticao()
    {
        $linha = [
            'id' => 1,
            'nome_completo' => 'Test Name',
            'sexo' => 'M',
            'data_nascimento' => '1990-01-01',
            'informacoes' => 'test info',
            'path_foto' => '/path/to/photo',
            'criado_em' => '2023-01-01 00:00:00.000000',
            'alterado_em' => '2023-01-01 00:00:00.000000',
            'competicao_id' => 1,
        ];

        $this->pdo->expects($this->once())
            ->method('query')
            ->willReturn($this->stmt);

        $this->stmt->method('getIterator')
            ->willReturn(new ArrayIterator([$linha]));

        $result = $this->repository->getAtletaCompeticao(1, 1);
        $this->assertIsArray($result);
        $this->assertInstanceOf(AtletaCompeticao::class, $result[0]);
    }

    public function testGetAtletasForaCompeticao()
    {
        $linha = [
            'id' => 1,
            'nome_completo' => 'Test Name',
            'sexo' => 'M',
            'data_nascimento' => '1990-01-01',
            'informacoes' => 'test info',
            'path_foto' => '/path/to/photo',
            'criado_em' => '2023-01-01 00:00:00.000000',
            'alterado_em' => '2023-01-01 00:00:00.000000',
        ];

        $this->pdo->expects($this->once())
            ->method('query')
            ->willReturn($this->stmt);

        $this->stmt->method('getIterator')
            ->willReturn(new ArrayIterator([$linha]));

        $result = $this->repository->getAtletasForaCompeticao(1, 1);
        $this->assertIsArray($result);
        $this->assertInstanceOf(Atleta::class, $result[0]);
    }

    public function testGetViaId()
    {
        $linha = [
            'sexo' => 'M',
            'categorias' => json_encode([1, 2, 3]),
            'sexo_duplas' => json_encode(['M', 'F']),
        ];

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$linha]);

        $result = $this->repository->getViaId(1, 1);
        $this->assertIsArray($result);
        $this->assertInstanceOf(Sexo::class, $result['sexo']);
        $this->assertIsArray($result['categorias']);
        $this->assertIsArray($result['sexoDuplas']);
    }

    public function testGetViaIdNull()
    {
        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $result = $this->repository->getViaId(1, 1);
        $this->assertNull($result);
    }

    public function testCadastrarAtletaCompetica()
    {
        $executePayload = [
            'atleta_id' => 1,
            'competicao_id' => 1,
            'informacoes' => 'info',
        ];

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->with($executePayload)
            ->willReturn(true);

        $atletaCompeticao = (new AtletaCompeticao())
            ->setAtleta((new Atleta())->setId(1))
            ->setCompeticao((new Competicao())->setId(1))
            ->setInformacao('info');

        $result = $this->repository->cadastrarAtletaCompeticao($atletaCompeticao);
        $this->assertTrue($result);
    }
}
