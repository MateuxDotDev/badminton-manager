<?php

namespace Tests;

use App\Util\General\SenhaCriptografada;
use PHPUnit\Framework\TestCase;

class SenhaCriptografadaTest extends TestCase
{
    public function testEncryptsAndValidates()
    {
        $email = 'tecnico@mail.com';
        $senha = 'tecnicobadminton123';

        $senhaCripto = SenhaCriptografada::criptografar($email, $senha);

        $this->assertTrue($senhaCripto->validar($email, $senha));
        $this->assertFalse($senhaCripto->validar($email.'.br', $senha));
        $this->assertFalse($senhaCripto->validar($email, $senha.'456'));
    }
}
