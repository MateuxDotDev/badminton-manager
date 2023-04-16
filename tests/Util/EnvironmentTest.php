<?php

namespace Tests\Util;

use App\Util\Environment;
use App\Util\EnvironmentAdapterInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $environmentMock = $this->createMock(EnvironmentAdapterInterface::class);
        $environmentMock->method('get')->willReturnMap([
            ['POSTGRES_USER', 'mock_user'],
            ['POSTGRES_PASSWORD', 'mock_password'],
            ['POSTGRES_DB', 'mock_db'],
            ['POSTGRES_HOST', 'mock_host'],
            ['POSTGRES_PORT', '5432'],
        ]);

        Environment::setEnvironmentAdapter($environmentMock);
    }

    public function testGetPostgresUser()
    {
        $this->assertEquals('mock_user', Environment::getPostgresUser());
    }

    public function testGetPostgresPassword()
    {
        $this->assertEquals('mock_password', Environment::getPostgresPassword());
    }

    public function testGetPostgresDb()
    {
        $this->assertEquals('mock_db', Environment::getPostgresDb());
    }

    public function testGetPostgresHost()
    {
        $this->assertEquals('mock_host', Environment::getPostgresHost());
    }

    public function testGetPostgresPort()
    {
        $this->assertEquals(5432, Environment::getPostgresPort());
    }
}
