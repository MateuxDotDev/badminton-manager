<?php

namespace Tests\Notificacao;

use App\Notificacao\Notificacao;
use App\Notificacao\TipoNotificacao;
use PHPUnit\Framework\TestCase;

class NotificacaoTest extends TestCase
{
    public function testSolicitacaoEnviada()
    {
        $idTecnico = 1;
        $idSolicitacao = 2;

        $notificacao = Notificacao::solicitacaoEnviada($idTecnico, $idSolicitacao);

        $this->assertEquals(TipoNotificacao::SOLICITACAO_ENVIADA, $notificacao->tipo);
        $this->assertEquals($idTecnico, $notificacao->idTecnico);
        $this->assertEquals($idSolicitacao, $notificacao->id1);
        $this->assertNull($notificacao->id2);
        $this->assertNull($notificacao->id3);
    }

    public function testSolicitacaoRecebida()
    {
        $idTecnico = 1;
        $idSolicitacao = 2;

        $notificacao = Notificacao::solicitacaoRecebida($idTecnico, $idSolicitacao);

        $this->assertEquals(TipoNotificacao::SOLICITACAO_RECEBIDA, $notificacao->tipo);
        $this->assertEquals($idTecnico, $notificacao->idTecnico);
        $this->assertEquals($idSolicitacao, $notificacao->id1);
        $this->assertNull($notificacao->id2);
        $this->assertNull($notificacao->id3);
    }
}
