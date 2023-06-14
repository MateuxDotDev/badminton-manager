<?php

namespace Tests\Competicoes;

use App\Competicoes\Competicao;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CompeticaoTest extends TestCase
{
    public function testSetAndGetId()
    {
        $competicao = new Competicao();
        $competicao->setId(10);
        $this->assertEquals(10, $competicao->id());
    }

    public function testSetAndGetNome()
    {
        $competicao = new Competicao();
        $competicao->setNome('Teste Nome');
        $this->assertEquals('Teste Nome', $competicao->nome());
    }

    public function testSetAndGetDescricao()
    {
        $competicao = new Competicao();
        $competicao->setDescricao('Teste descricao');
        $this->assertEquals('Teste descricao', $competicao->descricao());
    }

    public function testSetAndGetPrazo()
    {
        $prazo = new DateTimeImmutable();
        $competicao = new Competicao();
        $competicao->setPrazo($prazo);
        $this->assertEquals($prazo, $competicao->prazo());
    }

    public function testPrazoPassou()
    {
        $prazo = new DateTimeImmutable('yesterday');
        $competicao = new Competicao();
        $competicao->setPrazo($prazo);
        $this->assertTrue($competicao->prazoPassou());

        $prazo = new DateTimeImmutable('tomorrow');
        $competicao->setPrazo($prazo);
        $this->assertFalse($competicao->prazoPassou());
    }

    public function testToJson()
    {
        $prazo = new DateTimeImmutable();
        $competicao = new Competicao();
        $competicao->setId(10)
            ->setNome('Teste Nome')
            ->setPrazo($prazo)
            ->setDescricao('descrição teste');

        $expected = [
            'id' => 10,
            'nome' => 'Teste Nome',
            'prazo' => $prazo->format('Y-m-d'),
            'descricao' => 'descrição teste',
        ];

        $this->assertEquals($expected, $competicao->toJson());
    }
}