<?php

namespace Tests\Tecnico\Conta;

use App\Tecnico\Conta\LoginDTO;
use App\Util\Exceptions\ValidatorException;
use App\Util\Http\HttpStatus;
use PHPUnit\Framework\TestCase;

class LoginDTOTest extends TestCase
{
    /**
     * @throws ValidatorException
     */
    public function testParse(): void
    {
        $dados = [
            'email' => 'user@example.com',
            'senha' => 'password',
        ];

        $loginDTO = LoginDTO::parse($dados);

        $this->assertInstanceOf(LoginDTO::class, $loginDTO);
        $this->assertEquals('user@example.com', $loginDTO->email);
        $this->assertEquals('password', $loginDTO->senha);
    }

    public function testParseEmailInvalido(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionCode(HttpStatus::BAD_REQUEST->value);
        $this->expectExceptionMessage("Campo 'e-mail' inválido");

        $dados = [
            'email' => 'userexample.com',
            'senha' => 'password',
        ];

        LoginDTO::parse($dados);
    }

    public function testParseEmailFaltando(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionCode(HttpStatus::BAD_REQUEST->value);
        $this->expectExceptionMessage("Campo 'e-mail' faltando");

        $dados = [
            'senha' => 'password',
        ];

        LoginDTO::parse($dados);
    }

    public function testParseSenhaFaltando(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionCode(HttpStatus::BAD_REQUEST->value);
        $this->expectExceptionMessage("Campo 'senha' faltando");

        $dados = [
            'email' => 'user@example.com',
        ];

        LoginDTO::parse($dados);
    }
}
