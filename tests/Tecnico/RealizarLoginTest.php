<?php

namespace Tests\Tecnico;

use App\SenhaCriptografada;
use App\Session;
use App\Tecnico\Clube;
use App\Tecnico\Conta\LoginDTO;
use App\Tecnico\Conta\RealizarLogin;
use App\Tecnico\Tecnico;
use App\Tecnico\TecnicoRepository;
use App\Util\Dates;
use PHPUnit\Framework\TestCase;

class RealizarLoginTest extends TestCase
{
    private array $session = [];
    private RealizarLogin $realizarLogin;

    protected function setUp(): void
    {
        $session = new Session($this->session);

        $email1 = 'tec@ni.co';
        $senha1 = 'senha123';
        $senhaCripto1 = SenhaCriptografada::criptografar($email1, $senha1);

        $clube1 = (new Clube)
            ->setId(12)
            ->setNome('club')
            ->setDataCriacao(Dates::parseMicro('2023-03-10 01:02:03.123456'))
            ;

        $tec1 = (new Tecnico)
            ->setId(1)
            ->setNomeCompleto('técnico 1')
            ->setEmail($email1)
            ->setSenhaCriptografada($senhaCripto1)
            ->setClube($clube1)
            ;

        $email2 = 'bad@minton';
        $tec2 = (new Tecnico)
            ->setId(2)
            ->setNomeCompleto('fulano')
            ->setEmail($email2)
            ->setClube((new Clube)->setNome('outro clube'))
            ;

        $repo = $this->createMock(TecnicoRepository::class);
        $repo
            ->method('getViaEmail')
            ->will($this->returnCallback(function() use ($email1, $tec1, $email2, $tec2) {
                $email = func_get_arg(0);
                return match ($email) {
                    $email1 => $tec1,
                    $email2 => $tec2,
                    default => null
                };
            }));
        
        $this->realizarLogin = new RealizarLogin($repo, $session);
    }

    public function testLoginComSenhaOk()
    {
        $dto = new LoginDTO('tec@ni.co', 'senha123');
        $result = ($this->realizarLogin)($dto);

        $this->assertTrue($result->isOk());
        $this->assertNotEmpty($this->session);
        $this->assertArrayHasKey('tipo', $this->session);
        $this->assertEquals('tecnico', $this->session['tipo']);
        $this->assertArrayHasKey('tecnico', $this->session);

        $tecnico = unserialize($this->session['tecnico']);
        $this->assertInstanceOf(Tecnico::class, $tecnico);
    }

    public function testLoginSenhaIncorreta()
    {
        $dto = new LoginDTO('tec@ni.co', 'senhaIncorreta');
        $result = ($this->realizarLogin)($dto);
        $this->assertFalse($result->isOk());
        $this->assertEquals('Senha incorreta', $result->data());
    }
    
    public function testLoginSemSenha()
    {
        $dto = new LoginDTO('bad@minton', '');
        $result = ($this->realizarLogin)($dto);
        $this->assertFalse($result->isOk());
        $this->assertEquals('Técnico não tem senha', $result->data());
        $this->assertEmpty($this->session);
    }

    public function testLoginNaoEncontrado()
    {
        $dto = new LoginDTO('asdf@hjkl', '');
        $result = ($this->realizarLogin)($dto);
        $this->assertFalse($result->isOk());
        $this->assertEquals('Técnico não encontrado', $result->data());
        $this->assertEmpty($this->session);
    }
}
