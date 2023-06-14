<?php

namespace Tests\Tecnico\Solicitacao;

use App\Tecnico\Solicitacao\SolicitacaoPendente;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SolicitacaoPendenteTest extends TestCase
{
    public function testConstructor(): void
    {
        $dateTimeNow = new DateTimeImmutable();
        $solicitacao = new SolicitacaoPendente(
            id: 1,
            dataCriacao: $dateTimeNow,
            dataAlteracao: $dateTimeNow,
            idCompeticao: 2,
            idAtletaRemetente: 3,
            idAtletaDestinatario: 4,
            idCategoria: 5,
            informacoes: 'Teste'
        );

        $this->assertSame(1, $solicitacao->id);
        $this->assertSame($dateTimeNow, $solicitacao->dataCriacao);
        $this->assertSame($dateTimeNow, $solicitacao->dataAlteracao);
        $this->assertSame(2, $solicitacao->idCompeticao);
        $this->assertSame(3, $solicitacao->idAtletaRemetente);
        $this->assertSame(4, $solicitacao->idAtletaDestinatario);
        $this->assertSame(5, $solicitacao->idCategoria);
        $this->assertSame('Teste', $solicitacao->informacoes);
    }
}
