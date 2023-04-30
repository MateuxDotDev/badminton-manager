<?php
use App\Tecnico\Clube;
use PHPUnit\Framework\TestCase;


class ClubeTest extends TestCase
{
    public function testSettersAndGetters()
    {
        $clube = (new Clube);
        $this->assertNull($clube->id());
        $this->assertNull($clube->dataCriacao());
        $clube->setId(15);
        $this->assertEquals($clube->id(), 15);
        $dataCriacao = new DateTimeImmutable('2006-05-04 03:02:01.1234');
        $clube->setDataCriacao($dataCriacao);
        $this->assertEquals($clube->dataCriacao(), $dataCriacao);
        $clube->setNome('clube 123');
        $this->assertEquals($clube->nome(), 'clube 123');
    }

    public function testSerializeAndUnserialize()
    {
        $c0 = (new Clube)->setNome('clube 456');
        $c1 = unserialize(serialize($c0));
        $this->assertNull($c1->id());
        $this->assertEquals($c1->nome(), 'clube 456');
        $this->assertNull($c1->dataCriacao());

        $c0->setId(9);
        $c2 = unserialize(serialize($c0));
        $this->assertEquals($c2->id(), 9);
        $this->assertEquals($c2->nome(), 'clube 456');
        $this->assertNull($c2->dataCriacao());

        $dataCriacao = new DateTimeImmutable('now');
        $c0->setDataCriacao($dataCriacao);
        $c3 = unserialize(serialize($c0));
        $this->assertEquals($c3->id(), 9);
        $this->assertEquals($c3->nome(), 'clube 456');
        $this->assertEquals($c3->dataCriacao(), $dataCriacao);
    }
}