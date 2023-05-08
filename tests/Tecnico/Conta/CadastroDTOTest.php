<?php

namespace Tests\Tecnico\Conta;

use App\Tecnico\Conta\CadastroDTO;
use App\Util\Exceptions\ValidatorException;
use PHPUnit\Framework\TestCase;

class CadastroDTOTest extends TestCase
{
    public function testParse()
    {
        $req = [
            'email' => 'john.doe@example.com',
            'nome' => 'John Doe',
            'senha' => 'senha',
            'clube' => [
                'novo' => true,
                'nome' => 'Clube novo'
            ],
            'informacoes' => 'Informações do John Doe',
        ];

        $cadastroDTO = CadastroDTO::parse($req);

        $this->assertEquals('john.doe@example.com', $cadastroDTO->email);
        $this->assertEquals('John Doe', $cadastroDTO->nomeCompleto);
        $this->assertEquals('senha', $cadastroDTO->senha);
        $this->assertEquals('Clube novo', $cadastroDTO->nomeClubeNovo);
        $this->assertNull($cadastroDTO->idClubeExistente);
        $this->assertEquals('Informações do John Doe', $cadastroDTO->informacoes);
    }

    public function testParseCamposObrigatoriosFaltando()
    {
        $req = [];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Campo 'email' está faltando");

        CadastroDTO::parse($req);
    }

    public function testParseClubeCampoNovoFaltando()
    {
        $req = [
            'email' => 'john.doe@example.com',
            'nome' => 'John Doe',
            'senha' => 'senha',
            'clube' => [
                'nome' => 'Clube novo'
            ],
            'informacoes' => 'Informações do John Doe',
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Campo 'novo' em 'clube' está faltando");

        CadastroDTO::parse($req);
    }

    public function testParseClubeNomeFaltando()
    {
        $req = [
            'email' => 'john.doe@example.com',
            'nome' => 'John Doe',
            'senha' => 'senha',
            'clube' => [
                'novo' => true,
            ],
            'informacoes' => 'Informações do John Doe',
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Nome do clube novo está faltando");

        CadastroDTO::parse($req);
    }

    public function testParseClubeIdFaltando()
    {
        $req = [
            'email' => 'john.doe@example.com',
            'nome' => 'John Doe',
            'senha' => 'senha',
            'clube' => [
                'novo' => false,
            ],
            'informacoes' => 'Informações do John Doe',
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Id do clube existente está faltando");

        CadastroDTO::parse($req);
    }

    public function testParseEmailInvalido()
    {
        $req = [
            'email' => 'john.doe',
            'nome' => 'John Doe',
            'senha' => 'senha',
            'clube' => [
                'novo' => true,
                'nome' => 'Clube novo'
            ],
            'informacoes' => 'Informações do John Doe',
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('E-mail inválido');

        CadastroDTO::parse($req);
    }
}
