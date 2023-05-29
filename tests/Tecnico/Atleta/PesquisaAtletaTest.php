<?php

namespace Tests\Tecnico\Atleta;

use App\Tecnico\Atleta\Sexo;
use PHPUnit\Framework\TestCase;
use App\Tecnico\Atleta\PesquisaAtleta;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Ordenacao;

class PesquisaAtletaTest extends TestCase
{
    public function testCriaPesquisaAtleta(): void
    {
        $pesquisa = new PesquisaAtleta(
            1,
            'Nome Atleta',
            'Nome Técnico',
            'Clube',
            10,
            20,
            [1, 2, 3],
            ['M'],
            ['F'],
            'nomeAtleta',
            Ordenacao::ASC,
        );

        $this->assertSame(1, $pesquisa->idCompeticao);
        $this->assertSame('Nome Atleta', $pesquisa->nomeAtleta);
        $this->assertSame('Nome Técnico', $pesquisa->nomeTecnico);
        $this->assertSame('Clube', $pesquisa->clube);
        $this->assertSame(10, $pesquisa->idadeMaiorQue);
        $this->assertSame(20, $pesquisa->idadeMenorQue);
        $this->assertSame([1, 2, 3], $pesquisa->idCategorias);
        $this->assertSame(['M'], $pesquisa->sexoAtleta);
        $this->assertSame(['F'], $pesquisa->sexoDupla);
        $this->assertSame('nomeAtleta', $pesquisa->colunaOrdenacao);
        $this->assertSame(Ordenacao::ASC, $pesquisa->ordenacao);
    }

    /**
     * @throws ValidatorException
     */
    public function testParseValido(): void
    {
        $req = [
            'idCompeticao' => 1,
            'nomeAtleta' => 'Nome Atleta',
            'nomeTecnico' => 'Nome Técnico',
            'clube' => 'Clube',
            'idadeMaiorQue' => 10,
            'idadeMenorQue' => 20,
            'categorias' => [1, 2, 3],
            'sexoAtleta' => ['M'],
            'sexoDupla' => ['F'],
            'colunaOrdenacao' => 'nomeAtleta',
            'ordenacao' => 'asc',
        ];

        $pesquisa = PesquisaAtleta::parse($req);

        $this->assertSame(1, $pesquisa->idCompeticao);
        $this->assertSame('Nome Atleta', $pesquisa->nomeAtleta);
        $this->assertSame('Nome Técnico', $pesquisa->nomeTecnico);
        $this->assertSame('Clube', $pesquisa->clube);
        $this->assertSame(10, $pesquisa->idadeMaiorQue);
        $this->assertSame(20, $pesquisa->idadeMenorQue);
        $this->assertSame([1, 2, 3], $pesquisa->idCategorias);
        $this->assertSame([Sexo::MASCULINO], $pesquisa->sexoAtleta);
        $this->assertSame([Sexo::FEMININO], $pesquisa->sexoDupla);
        $this->assertSame('nomeAtleta', $pesquisa->colunaOrdenacao);
        $this->assertSame(Ordenacao::ASC, $pesquisa->ordenacao);
    }

    public function testParseSemIdCompeticao(): void
    {
        $req = [
            'nomeAtleta' => 'Nome Atleta',
            'nomeTecnico' => 'Nome Técnico',
            'clube' => 'Clube',
            'idadeMaiorQue' => 10,
            'idadeMenorQue' => 20,
            'categorias' => [1, 2, 3],
            'sexoAtleta' => ['M'],
            'sexoDupla' => ['F'],
            'colunaOrdenacao' => 'nomeAtleta',
            'ordenacao' => 'asc',
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('É obrigatório informar a competição que está sendo pesquisada');

        PesquisaAtleta::parse($req);
    }
}
