<?php

namespace Tests\Tecnico\Atleta\AtletaCompeticao;

use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use PHPUnit\Framework\TestCase;

class AtletaCompeticaoTest extends TestCase
{
    private AtletaCompeticao $atletaCompeticao;
    private Atleta $atleta;
    private Competicao $competicao;

    protected function setUp(): void
    {
        $this->atletaCompeticao = new AtletaCompeticao();
        $this->atleta = new Atleta();
        $this->competicao = new Competicao();
    }

    public function testSetAndGetAtleta(): void
    {
        $this->atletaCompeticao->setAtleta($this->atleta);
        $this->assertSame($this->atleta, $this->atletaCompeticao->atleta());
    }

    public function testSetAndGetCompeticao(): void
    {
        $this->atletaCompeticao->setCompeticao($this->competicao);
        $this->assertSame($this->competicao, $this->atletaCompeticao->competicao());
    }

    public function testSetAndGetInformacao(): void
    {
        $this->atletaCompeticao->setInformacao("informacao");
        $this->assertSame("informacao", $this->atletaCompeticao->informacao());
    }
}
