<?php

namespace Tests\Tecnico\Dupla;

use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Dupla\DuplaRepository;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

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
}
