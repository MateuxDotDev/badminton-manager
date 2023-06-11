<?php

namespace Tests\Tecnico\Atleta\AtletaCompeticao;

use App\Categorias\Categoria;
use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoCategoria;
use PHPUnit\Framework\TestCase;

class AtletaCompeticaoCategoriaTest extends TestCase
{
    private AtletaCompeticaoCategoria $atletaCompeticaoCategoria;
    private AtletaCompeticao $atletaCompeticao;
    private Categoria $categoria;

    protected function setUp(): void
    {
        $this->atletaCompeticao = new AtletaCompeticao();
        $this->categoria = new Categoria(1, "categoria", 10, 20);
        $this->atletaCompeticaoCategoria = new AtletaCompeticaoCategoria($this->atletaCompeticao, $this->categoria);
    }

    public function testSetAndGetAtletaCompeticao(): void
    {
        $newAtletaCompeticao = (new AtletaCompeticao())
            ->setAtleta(new Atleta())
            ->setCompeticao(new Competicao())
            ->setInformacao("informacao");

        $this->atletaCompeticaoCategoria->setAtletaCompeticao($newAtletaCompeticao);
        $this->assertSame($newAtletaCompeticao, $this->atletaCompeticaoCategoria->atletaCompeticao());
    }

    public function testSetAndGetCategoria(): void
    {
        $newCategoria = new Categoria(2, "new categoria", 12, 22);

        $this->atletaCompeticaoCategoria->setCategoria($newCategoria);
        $this->assertSame($newCategoria, $this->atletaCompeticaoCategoria->categoria());
    }
}
