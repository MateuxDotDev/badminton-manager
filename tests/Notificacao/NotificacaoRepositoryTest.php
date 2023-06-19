<?php

namespace Tests\Notificacao;

use App\Notificacao\Notificacao;
use App\Notificacao\NotificacaoRepository;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class NotificacaoRepositoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCriar(): void
    {
        $notificacao = Notificacao::solicitacaoEnviada(1, 2);

        $mockPDOStatement = $this->createMock(PDOStatement::class);
        $mockPDOStatement->expects($this->once())
            ->method('execute')
            ->with([
                'tipo'      => $notificacao->tipo->value,
                'idTecnico' => $notificacao->idTecnico,
                'id1'       => $notificacao->id1,
                'id2'       => $notificacao->id2,
                'id3'       => $notificacao->id3,
            ])
            ->willReturn(true);

        $mockPDO = $this->createMock(PDO::class);
        $mockPDO->expects($this->once())
            ->method('prepare')
            ->willReturn($mockPDOStatement);

        $mockPDO->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('123');

        $repository = new NotificacaoRepository($mockPDO);

        $this->assertEquals(123, $repository->criar($notificacao));
    }

    /**
     * @throws Exception
     */
    public function testCriarFalha(): void
    {
        $notificacao = Notificacao::solicitacaoEnviada(1, 2);

        $mockPDOStatement = $this->createMock(PDOStatement::class);
        $mockPDOStatement->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $mockPDO = $this->createMock(PDO::class);
        $mockPDO->expects($this->once())
            ->method('prepare')
            ->willReturn($mockPDOStatement);

        $repository = new NotificacaoRepository($mockPDO);

        $this->assertNull($repository->criar($notificacao));
    }

    /**
     * @throws Exception
     */
    public function testGetViaId1(): void
    {
        // Dados simulados que o método fetchAll deve retornar
        $fakeData = [
            [
                'id' => 1,
                'tipo' => 'tipo1',
                'tecnico_id' => 1,
                'id_1' => 1,
                'id_2' => 2,
                'id_3' => 3,
                'criado_em' => '2023-06-20 10:30:00',
            ],
            [
                'id' => 2,
                'tipo' => 'tipo2',
                'tecnico_id' => 2,
                'id_1' => 1,
                'id_2' => 4,
                'id_3' => 5,
                'criado_em' => '2023-06-20 10:35:00',
            ],
        ];

        $mockPDOStatement = $this->createMock(PDOStatement::class);
        $mockPDOStatement->expects($this->once())
            ->method('execute')
            ->with(['id_1' => 1])
            ->willReturn(true);

        $mockPDOStatement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($fakeData);

        $mockPDO = $this->createMock(PDO::class);
        $mockPDO->expects($this->once())
            ->method('prepare')
            ->willReturn($mockPDOStatement);

        $repository = new NotificacaoRepository($mockPDO);

        $result = $repository->getViaId1(1);

        $this->assertSame($fakeData, $result);
    }
}
