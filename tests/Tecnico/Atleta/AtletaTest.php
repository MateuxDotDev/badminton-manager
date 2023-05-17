<?php

namespace Tests\Tecnico\Atleta;

use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Tecnico;
use App\Tecnico\Atleta\Sexo;
use DateTime;
use PHPUnit\Framework\TestCase;

class AtletaTest extends TestCase
{
    private Atleta $atleta;
    private Tecnico $tecnico;

    protected function setUp(): void
    {
        $this->atleta = new Atleta();
        $this->tecnico = new Tecnico();
    }

    public function testSetTecnico(): void
    {
        $this->atleta->setTecnico($this->tecnico);
        $this->assertSame($this->tecnico, $this->atleta->tecnico());
    }

    public function testSetNomeCompleto(): void
    {
        $this->atleta->setNomeCompleto('Teste Nome');
        $this->assertSame('Teste Nome', $this->atleta->nomeCompleto());
    }

    public function testSetSexo(): void
    {
        $this->atleta->setSexo(Sexo::MASCULINO);
        $this->assertSame(Sexo::MASCULINO, $this->atleta->sexo());

        $this->atleta->setSexo(Sexo::FEMININO);
        $this->assertSame(Sexo::FEMININO, $this->atleta->sexo());
    }

    public function testSetDataNascimento(): void
    {
        $dataNascimento = new DateTime('2000-01-01');
        $this->atleta->setDataNascimento($dataNascimento);
        $this->assertSame($dataNascimento, $this->atleta->dataNascimento());
    }

    public function testSetInformacoesAdicionais(): void
    {
        $this->atleta->setInformacoesAdicionais('Teste Informacoes');
        $this->assertSame('Teste Informacoes', $this->atleta->informacoesAdicionais());
    }

    public function testSetFoto(): void
    {
        $this->atleta->setFoto('Teste Foto');
        $this->assertSame('Teste Foto', $this->atleta->foto());
    }
}
