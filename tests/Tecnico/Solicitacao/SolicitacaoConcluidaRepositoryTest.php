<?php

namespace Tests\Tecnico\Solicitacao;

use App\Tecnico\Solicitacao\SolicitacaoConcluidaRepository;
use App\Util\Exceptions\ValidatorException;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class SolicitacaoConcluidaRepositoryTest extends TestCase
{
    private PDO $pdo;
    private SolicitacaoConcluidaRepository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->repository = new SolicitacaoConcluidaRepository($this->pdo);
    }

    /**
     * @throws Exception
     */
    public function testExcluirPendente(): void
    {
        $id = 1;
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->with(['id' => $id]);

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $this->repository->excluirPendente($id);
    }

    /**
     * @throws Exception
     */
    public function testConcluirRejeitada(): void
    {
        $this->verifyConcluir('concluirRejeitada');
    }

    /**
     * @throws Exception
     */
    public function testConcluirAceita(): void
    {
        $this->verifyConcluir('concluirAceita');
    }

    /**
     * @throws Exception
     */
    public function testConcluirCancelada(): void
    {
        $this->verifyConcluir('concluirCancelada');
    }

    /**
     * @throws Exception
     */
    private function verifyConcluir(string $method): void
    {
        $id = 1;
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->expects($this->exactly(2))
            ->method('execute');
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => $id]]);
        $this->pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($stmt);

        $result = $this->repository->{$method}($id);

        $this->assertSame($id, $result);
    }

    /**
     * @throws Exception
     */
    public function testTransferirThrowsExceptionOnNoResults(): void
    {
        $this->expectException(ValidatorException::class);
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->expects($this->once())->method('execute');
        $stmt->expects($this->once())->method('fetchAll')->willReturn([]);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $this->repository->concluirRejeitada(1);
    }
}
