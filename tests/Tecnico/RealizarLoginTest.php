<?php

namespace Tests\Tecnico;

use App\Tecnico\Clube;
use App\Tecnico\Conta\LoginDTO;
use App\Tecnico\Conta\RealizarLogin;
use App\Tecnico\Tecnico;
use App\Tecnico\TecnicoRepository;
use App\Util\General\{Dates, SenhaCriptografada, UserSession};
use \Exception;
use \Throwable;
use PHPUnit\Framework\TestCase;
use App\Util\Exceptions\ValidatorException;

class RealizarLoginTest extends TestCase
{
    private array $session = [];
    private RealizarLogin $realizarLogin;

    /**
     * @throws Exception|\PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $session = new UserSession($this->session);

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
        $e = null;
        try {
            $dto = new LoginDTO('tec@ni.co', 'senha123');
            ($this->realizarLogin)($dto);
        } catch (Exception $e) {}

        $this->assertNull($e);
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
        $e = null;
        try {
            ($this->realizarLogin)($dto);
        } catch (Exception $e) {}
        $this->assertNotNull($e);
        $this->assertInstanceOf(ValidatorException::class, $e);
        $this->assertEquals('Senha incorreta', $e->getMessage());
    }
    
    public function testLoginSemSenha()
    {
        $e = null;
        try {
            $dto = new LoginDTO('bad@minton', '');
            ($this->realizarLogin)($dto);
        } catch (Exception $e) { }
        $this->assertNotNull($e);
        $this->assertInstanceOf(ValidatorException::class, $e);
        $this->assertEquals('Técnico não tem senha', $e->getMessage());
        $this->assertEmpty($this->session);
    }

    public function testLoginNaoEncontrado()
    {
        $e = null;
        try {
            $dto = new LoginDTO('asdf@hjkl', '');
            ($this->realizarLogin)($dto);
        } catch (Exception $e) { }
        $this->assertNotNull($e);
        $this->assertInstanceOf(ValidatorException::class, $e);
        $this->assertEquals('Técnico não encontrado', $e->getMessage());
        $this->assertEmpty($this->session);
    }
}
