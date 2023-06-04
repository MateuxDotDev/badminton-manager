<?php

namespace Tests\Tecnico\Atleta;

use App\Tecnico\Atleta\Sexo;
use PHPUnit\Framework\TestCase;
use App\Competicoes\PesquisaAtletaCompeticao;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Ordenacao;

class PesquisaAtletaCompeticaoTest extends TestCase
{
    public function testCriaPesquisaAtletaCompeticao(): void
    {
        $pesquisa = new PesquisaAtletaCompeticao(
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

        $pesquisa = PesquisaAtletaCompeticao::parse($req);

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

        PesquisaAtletaCompeticao::parse($req);
    }

    public function testParseSemOrdenacao(): void
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
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('É obrigatório informar a ordenação ao pesquisar atletas');

        PesquisaAtletaCompeticao::parse($req);
    }

    public function testParseComOrdenacaoInvalida(): void
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
            'ordenacao' => 'invalida',
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Ordenação inválida');

        PesquisaAtletaCompeticao::parse($req);
    }

    public function testParseComColunaOrdenacaoInvalida(): void
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
            'colunaOrdenacao' => 'invalida',
            'ordenacao' => 'asc',
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Coluna de ordenação inválida, deve ser uma dentre: nomeAtleta, nomeTecnico, clube, idade, dataAlteracao');

        PesquisaAtletaCompeticao::parse($req);
    }

    public function testParseComCamposNulos(): void
    {
        $req = [
            'idCompeticao' => 1,
            'colunaOrdenacao' => 'nomeAtleta',
            'ordenacao' => 'asc',
        ];

        $pesquisa = PesquisaAtletaCompeticao::parse($req);

        $this->assertSame(1, $pesquisa->idCompeticao);
        $this->assertNull($pesquisa->nomeAtleta);
        $this->assertNull($pesquisa->nomeTecnico);
        $this->assertNull($pesquisa->clube);
        $this->assertNull($pesquisa->idadeMaiorQue);
        $this->assertNull($pesquisa->idadeMenorQue);
        $this->assertEmpty($pesquisa->idCategorias);
        $this->assertEmpty($pesquisa->sexoAtleta);
        $this->assertEmpty($pesquisa->sexoDupla);
        $this->assertSame('nomeAtleta', $pesquisa->colunaOrdenacao);
        $this->assertInstanceOf(Ordenacao::class, $pesquisa->ordenacao);
        $this->assertSame(Ordenacao::ASC, $pesquisa->ordenacao);
    }
}
