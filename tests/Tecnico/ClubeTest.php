<?php

namespace Tests\Tecnico;

use App\Tecnico\Clube;
use PHPUnit\Framework\TestCase;

class ClubeTest extends TestCase
{
    private Clube $clube;

    protected function setUp(): void
    {
        $this->clube = new Clube();
    }

    public function testSetAndGetId()
    {
        $this->assertNull($this->clube->id());

        $id = 1;
        $this->clube->setId($id);
        $this->assertEquals($id, $this->clube->id());
    }

    public function testSetAndGetNome()
    {
        $this->assertNull($this->clube->nome());

        $nome = 'Clube A';
        $this->clube->setNome($nome);
        $this->assertEquals($nome, $this->clube->nome());
    }

    public function testSerialization()
    {
        $id = 1;
        $nome = 'Clube A';
        $dataCriacao = new \DateTimeImmutable();

        $this->clube
            ->setId($id)
            ->setNome($nome)
            ->setDataCriacao($dataCriacao);

        $serialized = $this->clube->__serialize();

        $this->assertArrayHasKey('id', $serialized);
        $this->assertArrayHasKey('nome', $serialized);
        $this->assertArrayHasKey('dataCriacao', $serialized);
        $this->assertEquals($id, $serialized['id']);
        $this->assertEquals($nome, $serialized['nome']);
        $this->assertEquals($dataCriacao->format('Y-m-d H:i:s.u'), $serialized['dataCriacao']);

        $clube = new Clube();
        $clube->__unserialize($serialized);
        $this->assertEquals($id, $clube->id());
        $this->assertEquals($nome, $clube->nome());
        $this->assertEquals($dataCriacao->getTimestamp(), $clube->dataCriacao()->getTimestamp());
    }
}
