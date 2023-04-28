<?php

namespace Tests\Admin\Login;

use App\Admin\Login\Login;
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    public function testGetUsuario()
    {
        $login = new Login('TestUser', 'TestPassword');
        $this->assertEquals('TestUser', $login->getUsuario());
    }

    public function testGetSenha()
    {
        $login = new Login('TestUser', 'TestPassword');
        $this->assertEquals('TestPassword', $login->getSenha());
    }

    public function testGetBeforeHash()
    {
        $login = new Login('TestUser', 'TestPassword');
        $salt = 'TestSalt';
        $expected = 'TestUserTestPasswordTestSalt';
        $this->assertEquals($expected, $login->getBeforeHash($salt));
    }

    public function testGerarHash()
    {
        $login = new Login('TestUser', 'TestPassword');
        $salt = 'TestSalt';
        $beforeHash = $login->getBeforeHash($salt);
        $hash = $login->gerarHash($salt, 4);
        $this->assertTrue(password_verify($beforeHash, $hash));
    }
}
