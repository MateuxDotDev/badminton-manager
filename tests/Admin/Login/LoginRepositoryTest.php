<?php

namespace Tests\Admin\Login;

use App\Admin\Login\Login;
use App\Admin\Login\LoginRepository;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class LoginRepositoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testValidateLoginUserExistsAndPasswordCorrect()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $pdo->method('prepare')
            ->willReturn($stmt);

        $user = [
            'hash_senha' => password_hash('TestUserTestPasswordTestSalt', PASSWORD_BCRYPT, ['cost' => 4]),
            'salt_senha' => 'TestSalt',
        ];

        $stmt->method('fetch')
            ->willReturn($user);

        $login = new Login('TestUser', 'TestPassword');

        $repo = new LoginRepository($pdo);
        $result = $repo->validateLogin($login);

        $this->assertTrue($result);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testValidateLoginUserDoesNotExistOrPasswordIncorrect()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $pdo->method('prepare')
            ->willReturn($stmt);

        $stmt->method('fetch')
            ->willReturn(false);

        $login = new Login('TestUser', 'TestPassword');

        $repo = new LoginRepository($pdo);
        $result = $repo->validateLogin($login);

        $this->assertFalse($result);
    }
}
