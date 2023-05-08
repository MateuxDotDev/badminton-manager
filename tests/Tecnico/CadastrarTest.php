<?php

use App\Tecnico\Conta\Cadastrar;
use App\Tecnico\Conta\CadastroDTO;
use App\Util\General\Dates;
use App\Tecnico\Clube;
use App\Tecnico\Tecnico;
use App\Tecnico\TecnicoRepositoryArray;
use PHPUnit\Framework\TestCase;
use App\Util\Exceptions\ValidatorException;

// TODO quando implementar funcionalidade de vincular técnico
// com clube existente, testar

class CadastrarTest extends TestCase
{
    private Cadastrar $cadastrar;

    protected function setUp(): void
    {
        $repo = new TecnicoRepositoryArray();

        $data1 = Dates::parseMicro("2023-10-10 12:13:14.999999");
        $clube1 = (new Clube)
            ->setNome('Clube de Rio do Sul')
            ->setDataCriacao($data1)
            ;
        $tec1 = (new Tecnico)
            ->setEmail('tecnico@badminton.com')
            ->setNomeCompleto('Fulano da Silva')
            ->setDataCriacao($data1)
            ->setDataAlteracao($data1)
            ->setClube($clube1)
            ;
        $repo->criarTecnico($tec1);

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
                nomeClubeNovo: 'Outro clube',
                idClubeExistente: null,
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
                nomeClubeNovo: 'Outro clube',
                idClubeExistente: null,
                informacoes: '',
            );
            $data = ($this->cadastrar)($dto);
        } catch (Exception $e) { }
        $this->assertNull($e);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('idClube', $data);
        $this->assertEquals($data['id'], 2);
        $this->assertEquals($data['idClube'], 2);
    }

    public function testCadastraComClubeExistente(): void
    {
        $e = null;
        try {
            $dto = new CadastroDTO(
                email: 'email@novo.com',
                nomeCompleto: 'Beltrano',
                senha: 'senhaMuitoSegura123',
                nomeClubeNovo: null,
                idClubeExistente: 1,
                informacoes: '',
            );
            $data = ($this->cadastrar)($dto);
        } catch (Exception $e) { }
        $this->assertNull($e);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('idClube', $data);
        $this->assertEquals($data['id'], 2);
        $this->assertEquals($data['idClube'], 1);
    }
}