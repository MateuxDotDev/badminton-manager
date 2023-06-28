<?php

namespace Tests\Tecnico\Solicitacao;

use App\Tecnico\Solicitacao\SolicitacaoConcluida;
use PHPUnit\Framework\TestCase;

class SolicitacaoConcluidaTest extends TestCase
{
    private const DUMMY_DATA = [
        'id' => 1,
        'competicao_id' => 2,
        'atleta_origem_id' => 3,
        'atleta_destino_id' => 4,
        'informacoes' => 'Informações de Teste',
        'categoria_id' => 5,
        'criado_em' => '2023-06-28 15:30:00.000000',
        'alterado_em' => '2023-06-28 16:30:00.000000'
    ];

    public function testCreateFromRow(): void
    {
        $solicitacao = SolicitacaoConcluida::fromRow(self::DUMMY_DATA);

        $this->assertInstanceOf(SolicitacaoConcluida::class, $solicitacao);
        $this->assertEquals(self::DUMMY_DATA['id'], $solicitacao->id());
        $this->assertEquals(self::DUMMY_DATA['competicao_id'], $solicitacao->competicaoId());
        $this->assertEquals(self::DUMMY_DATA['atleta_origem_id'], $solicitacao->atletaOrigemId());
        $this->assertEquals(self::DUMMY_DATA['atleta_destino_id'], $solicitacao->atletaDestinoId());
        $this->assertEquals(self::DUMMY_DATA['informacoes'], $solicitacao->informacoes());
        $this->assertEquals(self::DUMMY_DATA['categoria_id'], $solicitacao->categoriaId());
        $this->assertEquals(self::DUMMY_DATA['criado_em'], $solicitacao->criadoEm()->format('Y-m-d H:i:s.u'));
        $this->assertEquals(self::DUMMY_DATA['alterado_em'], $solicitacao->alteradoEm()->format('Y-m-d H:i:s.u'));
    }
}
