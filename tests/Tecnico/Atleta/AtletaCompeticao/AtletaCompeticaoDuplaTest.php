<?php

namespace Tests\Tecnico\Atleta\AtletaCompeticao;

use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoDupla;
use PHPUnit\Framework\TestCase;

class AtletaCompeticaoDuplaTest extends TestCase
{
    private AtletaCompeticaoDupla $atletaCompeticaoDupla;
    private AtletaCompeticao $atletaCompeticao;
    private Sexo $tipoDupla;

    protected function setUp(): void
    {
        $this->atletaCompeticao = (new AtletaCompeticao())
            ->setAtleta(new Atleta())
            ->setCompeticao(new Competicao())
            ->setInformacao('Teste Informacao');

        $this->tipoDupla = Sexo::MASCULINO;

        $this->atletaCompeticaoDupla = (new AtletaCompeticaoDupla())
            ->setAtletaCompeticao($this->atletaCompeticao)
            ->setTipoDupla($this->tipoDupla);
    }

    public function testSetAndGetAtletaCompeticao(): void
    {
        $this->assertSame($this->atletaCompeticao, $this->atletaCompeticaoDupla->atletaCompeticao());
    }

    public function testSetAndGetTipoDupla(): void
    {
        $this->assertSame($this->tipoDupla, $this->atletaCompeticaoDupla->tipoDupla());
    }
}
