<?php

namespace Tests\Tecnico\Atleta\AtletaCompeticao;

use App\Categorias\Categoria;
use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\Sexo;
use DateTime;
use PHPUnit\Framework\TestCase;

class AtletaCompeticaoTest extends TestCase
{
    public function testAtletaCompeticaoCreation(): void
    {
        $atletaCompeticao = new AtletaCompeticao();
        $this->assertInstanceOf(AtletaCompeticao::class, $atletaCompeticao);
    }

    public function testSetAtleta(): void
    {
        $atleta = new Atleta();
        $atletaCompeticao = new AtletaCompeticao();

        $atletaCompeticao->setAtleta($atleta);

        $this->assertEquals($atleta, $atletaCompeticao->atleta());
    }

    public function testSetCompeticao(): void
    {
        $competicao = new Competicao();
        $atletaCompeticao = new AtletaCompeticao();

        $atletaCompeticao->setCompeticao($competicao);

        $this->assertEquals($competicao, $atletaCompeticao->competicao());
    }

    public function testSetInformacao(): void
    {
        $atletaCompeticao = new AtletaCompeticao();
        $atletaCompeticao->setInformacao('Teste');

        $this->assertEquals('Teste', $atletaCompeticao->informacao());
    }

    public function testAddCategoria(): void
    {
        $atletaCompeticao = new AtletaCompeticao();
        $categoria = new Categoria(1, 'Categoria 1', 10, 20);

        $atletaCompeticao->addCategoria($categoria);

        $this->assertTrue($atletaCompeticao->jogaEmCategoria(1));
        $this->assertFalse($atletaCompeticao->jogaEmCategoria(2));
    }

    public function testAddSexoDupla(): void
    {
        $atletaCompeticao = new AtletaCompeticao();

        $atletaCompeticao->addSexoDupla(Sexo::MASCULINO);

        $this->assertTrue($atletaCompeticao->buscaDuplaDoSexo(Sexo::MASCULINO));
        $this->assertFalse($atletaCompeticao->buscaDuplaDoSexo(Sexo::FEMININO));
    }

    public function testTemDataCriacao(): void
    {
        $atletaCompeticao = new AtletaCompeticao();
        $dataCriacao = new DateTime();

        $atletaCompeticao->setDataCriacao($dataCriacao);

        $this->assertEquals($dataCriacao, $atletaCompeticao->dataCriacao());
    }

    public function testTemDataAlteracao(): void
    {
        $atletaCompeticao = new AtletaCompeticao();
        $dataAlteracao = new DateTime();

        $atletaCompeticao->setDataAlteracao($dataAlteracao);

        $this->assertEquals($dataAlteracao, $atletaCompeticao->dataAlteracao());
    }

    public function testPodeParticipar(): void
    {
        $atletaCompeticao = new AtletaCompeticao();
        $categoria = new Categoria(1, 'Categoria 1', 10, 20);

        $dataNascimento = new DateTime('2005-01-01');
        $dataCompeticao = new DateTime('2023-06-01');

        $this->assertTrue($categoria->podeParticipar($dataNascimento, $dataCompeticao));
    }
}
