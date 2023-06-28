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

    public function testSolicitacaoRecebidaRejeitada()
    {
        $idTecnico = 1;
        $idSolicitacao = 2;

        $notificacao = Notificacao::solicitacaoRecebidaRejeitada($idTecnico, $idSolicitacao);

        $this->assertEquals(TipoNotificacao::SOLICITACAO_RECEBIDA_REJEITADA, $notificacao->tipo);
        $this->assertEquals($idTecnico, $notificacao->idTecnico);
        $this->assertEquals($idSolicitacao, $notificacao->id1);
        $this->assertNull($notificacao->id2);
        $this->assertNull($notificacao->id3);
    }

    public function testSolicitacaoEnviadaRejeitada()
    {
        $idTecnico = 1;
        $idSolicitacao = 2;

        $notificacao = Notificacao::solicitacaoEnviadaRejeitada($idTecnico, $idSolicitacao);

        $this->assertEquals(TipoNotificacao::SOLICITACAO_ENVIADA_REJEITADA, $notificacao->tipo);
        $this->assertEquals($idTecnico, $notificacao->idTecnico);
        $this->assertEquals($idSolicitacao, $notificacao->id1);
        $this->assertNull($notificacao->id2);
        $this->assertNull($notificacao->id3);
    }

    public function testSolicitacaoEnviadaCancelada()
    {
        $idTecnico = 1;
        $idSolicitacao = 2;

        $notificacao = Notificacao::solicitacaoEnviadaCancelada($idTecnico, $idSolicitacao);

        $this->assertEquals(TipoNotificacao::SOLICITACAO_ENVIADA_CANCELADA, $notificacao->tipo);
        $this->assertEquals($idTecnico, $notificacao->idTecnico);
        $this->assertEquals($idSolicitacao, $notificacao->id1);
        $this->assertNull($notificacao->id2);
        $this->assertNull($notificacao->id3);
    }

    public function testSolicitacaoRecebidaAceita()
    {
        $idTecnico = 1;
        $idSolicitacao = 2;

        $notificacao = Notificacao::solicitacaoRecebidaAceita($idTecnico, $idSolicitacao);

        $this->assertEquals(TipoNotificacao::SOLICITACAO_RECEBIDA_ACEITA, $notificacao->tipo);
        $this->assertEquals($idTecnico, $notificacao->idTecnico);
        $this->assertEquals($idSolicitacao, $notificacao->id1);
        $this->assertNull($notificacao->id2);
        $this->assertNull($notificacao->id3);
    }

    public function testSolicitacaoEnviadaAceita()
    {
        $idTecnico = 1;
        $idSolicitacao = 2;

        $notificacao = Notificacao::solicitacaoEnviadaAceita($idTecnico, $idSolicitacao);

        $this->assertEquals(TipoNotificacao::SOLICITACAO_ENVIADA_ACEITA, $notificacao->tipo);
        $this->assertEquals($idTecnico, $notificacao->idTecnico);
        $this->assertEquals($idSolicitacao, $notificacao->id1);
        $this->assertNull($notificacao->id2);
        $this->assertNull($notificacao->id3);
    }

    public function testInclusaoCompeticao(): void
    {
        $idTecnico = 1;
        $idCompeticao = 1;

        $notificacao = Notificacao::inclusaoCompeticao($idTecnico, $idCompeticao);

        $this->assertEquals(TipoNotificacao::ATLETA_INCLUIDO_NA_COMPETICAO, $notificacao->tipo);
        $this->assertEquals($idTecnico, $notificacao->idTecnico);
        $this->assertEquals($idCompeticao, $notificacao->id1);
        $this->assertNull($notificacao->id2);
        $this->assertNull($notificacao->id3);
    }
}


