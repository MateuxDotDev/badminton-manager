<?php

namespace Tests\Tecnico\Solicitacao;

use App\Tecnico\Solicitacao\SolicitacaoConcluidaRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\Http\HttpStatus;
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

    /**
     * @throws ValidatorException
     * @throws Exception
     */
    public function testGetViaId(): void
    {
        $id = 1;
        $stmt = $this->createMock(PDOStatement::class);

        $rows = [
            [
                'id' => $id,
                'competicao_id' => 1,
                'atleta_origem_id' => 1,
                'atleta_destino_id' => 2,
                'informacoes' => 'informacoes',
                'categoria_id' => 1,
                'criado_em' => '2021-01-01 00:00:00.000000',
                'alterado_em' => '2021-01-01 00:00:00.000000',
            ]
        ];

        $stmt->expects($this->once())->method('execute');
        $stmt->expects($this->once())->method('fetchAll')->willReturn($rows);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $result = $this->repository->getViaId($id);

        $this->assertSame($id, $result->id());
        $this->assertSame($rows[0]['competicao_id'], $result->competicaoId());
        $this->assertSame($rows[0]['atleta_origem_id'], $result->atletaOrigemId());
        $this->assertSame($rows[0]['atleta_destino_id'], $result->atletaDestinoId());
        $this->assertSame($rows[0]['informacoes'], $result->informacoes());
        $this->assertSame($rows[0]['categoria_id'], $result->categoriaId());
        $this->assertSame($rows[0]['criado_em'], $result->criadoEm()->format('Y-m-d H:i:s.u'));
        $this->assertSame($rows[0]['alterado_em'], $result->alteradoEm()->format('Y-m-d H:i:s.u'));
    }

    /**
     * @throws ValidatorException
     * @throws Exception
     */
    public function testGetViaIdNaoEncontrada(): void
    {
        $id = 1;
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->expects($this->once())->method('execute');
        $stmt->expects($this->once())->method('fetchAll')->willReturn([]);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Solicitação não encontrada');
        $this->expectExceptionCode(HttpStatus::NOT_FOUND->value);

        $this->repository->getViaId($id);
    }
}
