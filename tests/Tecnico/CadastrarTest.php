<?php

use App\Tecnico\Conta\Cadastrar;
use App\Tecnico\Conta\CadastroDTO;
use App\Tecnico\Tecnico;
use App\Tecnico\TecnicoRepositoryArray;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use PHPUnit\Framework\TestCase;

class CadastrarTest extends TestCase
{
    private Cadastrar $cadastrar;

    protected function setUp(): void
    {
        $repo = new TecnicoRepositoryArray();

        $data1 = Dates::parseMicro("2023-10-10 12:13:14.999999");
        $tec1 = (new Tecnico)
            ->setEmail('tecnico@badminton.com')
            ->setNomeCompleto('Fulano da Silva')
            ->setDataCriacao($data1)
            ->setDataAlteracao($data1)
            ;
        $repo->criarTecnico($tec1, 'Clube de Rio do Sul');

        $this->cadastrar = new Cadastrar($repo);
    }

    public function testProibeDuplicarEmail(): void
    {
        $e = null;
        try {
            $dto = new CadastroDTO(
                email: 'tecnico@badminton.com',
                nomeCompleto: 'Outro técnico',
                senha: 'senhaSegura123',
                nomeClube: 'Outro clube',
                informacoes: '',
            );
            ($this->cadastrar)($dto);
        } catch (Exception $e) { }
        $this->assertNotNull($e);
        $this->assertInstanceOf(ValidatorException::class, $e);
        $this->assertEquals('Esse e-mail já está sendo usado por outro técnico', $e->getMessage());
    }

    public function testCadastraComClubeNovo(): void
    {
        $e = null;
        try {
            $dto = new CadastroDTO(
                email: 'email@novo.com',
                nomeCompleto: 'Outro técnico',
                senha: 'senhaSegura123',
                nomeClube: 'Outro clube',
                informacoes: '',
            );
            $data = ($this->cadastrar)($dto);
        } catch (Exception $e) { }
        $this->assertNull($e);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('idClube', $data);
        $this->assertEquals(2, $data['id']);
        $this->assertEquals(2, $data['idClube']);
    }

    public function testCadastraComClubeExistente(): void
    {
        $e = null;
        try {
            $dto = new CadastroDTO(
                email: 'email@novo.com',
                nomeCompleto: 'Beltrano',
                senha: 'senhaMuitoSegura123',
                nomeClube: 'Clube de Rio do Sul',
                informacoes: '',
            );
            $data = ($this->cadastrar)($dto);
        } catch (Exception $e) { }
        $this->assertNull($e);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('idClube', $data);
        $this->assertEquals(2, $data['id']);
        $this->assertEquals(1, $data['idClube']);
    }
}