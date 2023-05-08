<?php

namespace Tests\Admin;

use App\Admin\Admin;
use App\Util\General\SenhaCriptografada;
use PHPUnit\Framework\TestCase;

class AdminTest extends TestCase
{
    public function testSetAndGetNome()
    {
        $admin = new Admin();
        $nome = 'John Doe';

        $admin->setNome($nome);

        $this->assertSame($nome, $admin->nome(), 'O nome do administrador deve ser igual ao valor definido.');
    }

    public function testSetAndGetSenhaCriptografada()
    {
        $admin = new Admin();
        $senhaCripto = new SenhaCriptografada('senha_criptografada_exemplo', 'salt_exemplo');

        $admin->setSenhaCriptografada($senhaCripto);

        $this->assertSame($senhaCripto, $admin->senhaCriptografada(), 'A senha criptografada do administrador deve ser igual ao valor definido.');
    }

    public function testSetAndGetNullSenhaCriptografada()
    {
        $admin = new Admin();

        $admin->setSenhaCriptografada(null);

        $this->assertNull($admin->senhaCriptografada(), 'A senha criptografada do administrador deve ser nula quando o valor definido for nulo.');
    }
}
