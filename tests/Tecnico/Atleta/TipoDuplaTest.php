<?php

namespace Tests\Tecnico\Atleta;

use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Atleta\TipoDupla;
use PHPUnit\Framework\TestCase;

class TipoDuplaTest extends TestCase
{
    public function testToString(): void
    {
        $this->assertSame('masculina', TipoDupla::MASCULINA->toString());
        $this->assertSame('feminina', TipoDupla::FEMININA->toString());
        $this->assertSame('mista', TipoDupla::MISTA->toString());
    }

    public function testCriar(): void
    {
        $this->assertEquals(TipoDupla::MASCULINA, TipoDupla::criar(Sexo::MASCULINO, Sexo::MASCULINO));
        $this->assertEquals(TipoDupla::FEMININA, TipoDupla::criar(Sexo::FEMININO, Sexo::FEMININO));
        $this->assertEquals(TipoDupla::MISTA, TipoDupla::criar(Sexo::MASCULINO, Sexo::FEMININO));
        $this->assertEquals(TipoDupla::MISTA, TipoDupla::criar(Sexo::FEMININO, Sexo::MASCULINO));
    }
}
