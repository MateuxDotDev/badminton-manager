<?php

namespace Tests\Tecnico\Dupla;

use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Dupla\DuplaRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\Http\HttpStatus;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class DuplaRepositoryTest extends TestCase
{
    private PDO $pdo;
    private DuplaRepository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->repository = new DuplaRepository($this->pdo);
    }

    /**
     * @throws Exception
     */
    public function testCriarDupla(): void
    {
        $idCompeticao = 1;
        $idAtleta1 = 2;
        $idAtleta2 = 3;
        $idCategoria = 4;
        $idSolicitacaoOrigem = 5;

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->with([
            'competicao_id'  => $idCompeticao,
            'categoria_id'   => $idCategoria,
            'atleta1_id'     => $idAtleta1,
            'atleta2_id'     => $idAtleta2,
            'solicitacao_id' => $idSolicitacaoOrigem,
        ]);

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $this->repository->criarDupla(
            $idCompeticao,
            $idAtleta1,
            $idAtleta2,
            $idCategoria,
            $idSolicitacaoOrigem,
        );
    }

    /**
     * @throws Exception
     */
    public function testTemDuplaFalso(): void
    {
        $idCompeticao = 1;
        $idAtleta = 2;
        $idCategoria = 3;
        $sexo = Sexo::MASCULINO;

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->with([
            'competicao_id' => $idCompeticao,
            'categoria_id'  => $idCategoria,
            'atleta_id'     => $idAtleta,
            'sexo'          => $sexo->value,
        ]);

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(0);

        $result = $this->repository->temDupla(
            $idCompeticao,
            $idAtleta,
            $idCategoria,
            $sexo,
        );

        $this->assertFalse($result);
    }

    /**
     * @throws Exception
     */
    public function testTemDuplaVerdadeiro(): void
    {
        $idCompeticao = 1;
        $idAtleta = 2;
        $idCategoria = 3;
        $sexo = Sexo::MASCULINO;

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->with([
            'competicao_id' => $idCompeticao,
            'categoria_id'  => $idCategoria,
            'atleta_id'     => $idAtleta,
            'sexo'          => $sexo->value,
        ]);

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $result = $this->repository->temDupla(
            $idCompeticao,
            $idAtleta,
            $idCategoria,
            $sexo,
        );

        $this->assertTrue($result);
    }

    public function testFormadas(): void
    {
        $idCompeticao = 1;

        $row = [
            'id' => 1,
            'idSolicitacao' => 1,
            'categoria' => 'Sub 11',
            'atletas' => json_encode([
                [
                    'id' => 1,
                    'nome' => 'Atleta 1',
                    'sexo' => 'M',
                    'dataNascimento' => '2000-01-01',
                    'foto' => 'foto.jpg',
                    'tecnico' => [
                        'id' => 1,
                        'nome' => 'Tecnico 1',
                        'clube' => 'Clube 1',
                    ]
                ],
                [
                    'id' => 2,
                    'nome' => 'Atleta 2',
                    'sexo' => 'F',
                    'dataNascimento' => '1999-01-01',
                    'foto' => 'foto.jpg',
                    'tecnico' => [
                        'id' => 2,
                        'nome' => 'Tecnico 2',
                        'clube' => 'Clube 2',
                    ]
                ],
            ]),
        ];

        $stmt = $this->createMock(PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['competicao_id' => $idCompeticao]);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$row]);

        $result = $this->repository->formadas($idCompeticao);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $atletas = $result[0]['atletas'];
        $this->assertIsArray($atletas);
        $this->assertCount(2, $atletas);
        $tecnicos = array_map(fn($atleta) => $atleta['tecnico'], $atletas);
        $this->assertIsArray($tecnicos);
        $this->assertCount(2, $tecnicos);
        $idades = array_map(fn($atleta) => $atleta['idade'], $atletas);
        $this->assertIsArray($idades);
        $this->assertCount(2, $idades);
    }

    /**
     * @throws ValidatorException
     * @throws Exception
     */
    public function testGetViaAtletas(): void
    {
        $row = [
            'id' => 1,
            'idSolicitacao' => 1,
            'criadoEm' => '2021-01-01 00:00:00.000000',
            'categoria' => 'Sub 17',
            'categoriaId' => 1,
            'competicao' => 'Campeonato Mundial',
            'competicaoId' => 1,
            'atletas' => json_encode([
                [
                    'id' => 1,
                    'nome' => 'Atleta 1',
                    'sexo' => 'M',
                    'dataNascimento' => '2000-01-01',
                    'foto' => 'foto.jpg',
                    'informacoes' => 'Informações',
                    'tecnico' => [
                        'id' => 1,
                        'nome' => 'Tecnico 1',
                        'email' => 'tecnico1@mail.com',
                        'informacoes' => 'Informações',
                        'clubeId' => 1,
                        'clube' => 'Clube 1'
                    ]
                ],
                [
                    'id' => 2,
                    'nome' => 'Atleta 2',
                    'sexo' => 'F',
                    'dataNascimento' => '2000-01-01',
                    'foto' => 'foto.jpg',
                    'informacoes' => 'Informações',
                    'tecnico' => [
                        'id' => 2,
                        'nome' => 'Tecnico 2',
                        'email' => 'tecnico2@mail.com',
                        'informacoes' => 'Informações',
                        'clubeId' => 2,
                        'clube' => 'Clube 2'
                    ]
                ]
            ])
        ];

        $stmt = $this->createMock(PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['atleta1' => 1, 'atleta2' => 2]);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$row]);

        $dupla = $this->repository->getViaAtletas(1, 2);

        $this->assertEquals(1, $dupla->id());
        $this->assertEquals(1, $dupla->idSolicitacao());
        $this->assertEquals('Sub 17', $dupla->categoria()->descricao());
        $this->assertEquals(1, $dupla->categoria()->id());
        $this->assertEquals('Campeonato Mundial', $dupla->competicao()->nome());
        $this->assertEquals(1, $dupla->atleta1()->id());
        $this->assertEquals(2, $dupla->atleta2()->id());
        $this->assertEquals(1, $dupla->atletaFromTecnico(1)->id());
        $this->assertEquals(2, $dupla->other(1)->id());
    }

    /**
     * @throws Exception
     */
    public function testGetViaAtletasNaoEncontrada(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['atleta1' => 1, 'atleta2' => 2]);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Dupla não encontrada');
        $this->expectExceptionCode(HttpStatus::NOT_FOUND->value);

        $this->repository->getViaAtletas(1, 2);
    }

    /**
     * @throws Exception
     */
    public function testGetViaAtletasDuplicada(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['atleta1' => 1, 'atleta2' => 2]);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([[], []]);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Dupla duplicada');
        $this->expectExceptionCode(HttpStatus::INTERNAL_SERVER_ERROR->value);

        $this->repository->getViaAtletas(1, 2);
    }
}
