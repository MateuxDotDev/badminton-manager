<?php

namespace Tests\Notificacao;

use App\Notificacao\Notificacao;
use App\Notificacao\NotificacaoRepository;
use App\Notificacao\TipoNotificacao;
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
}
