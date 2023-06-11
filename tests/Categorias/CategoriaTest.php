<?php

namespace Tests\Categorias;

use App\Categorias\Categoria;
use DateTime;
use PHPUnit\Framework\TestCase;

class CategoriaTest extends TestCase
{
    public function testCriaCategoria(): void
    {
        $categoria = new Categoria(1, 'Categoria Teste', 10, 20);

        $this->assertSame(1, $categoria->id());
        $this->assertSame('Categoria Teste', $categoria->descricao());
        $this->assertSame(10, $categoria->idadeMaiorQue());
        $this->assertSame(20, $categoria->idadeMenorQue());
    }

    public function testPodeParticipar(): void
    {
        $categoria = new Categoria(1, 'Categoria Teste', 10, 20);

        $dataNascimento = new DateTime('2010-01-01');
        $dataCompeticao = new DateTime('2023-01-01');

        $this->assertTrue($categoria->podeParticipar($dataNascimento, $dataCompeticao));

        $dataNascimento = new DateTime('2002-01-01');

        $this->assertFalse($categoria->podeParticipar($dataNascimento, $dataCompeticao));

        $dataNascimento = new DateTime('2026-01-01');

        $this->assertFalse($categoria->podeParticipar($dataNascimento, $dataCompeticao));
    }
}
