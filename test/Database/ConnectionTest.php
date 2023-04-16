<?php

namespace Test\Database;

use App\Database\Connection;
use PDO;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $pdoMock = $this->createMock(PDO::class);
        Connection::setInstance($pdoMock);
    }

    protected function tearDown(): void
    {
        Connection::setInstance(null);
    }

    public function testGetInstanceReturnsMockedPdoInstance()
    {
        $pdoInstance = Connection::getInstance();

        $this->assertInstanceOf(PDO::class, $pdoInstance, 'The getInstance() method should return an instance of PDO.');
        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $pdoInstance, 'The getInstance() method should return a mocked PDO instance.');
    }

    public function testGetInstanceReturnsSameMockedPdoInstance()
    {
        $pdoInstance1 = Connection::getInstance();
        $pdoInstance2 = Connection::getInstance();

        $this->assertSame($pdoInstance1, $pdoInstance2, 'The getInstance() method should return the same PDO instance on subsequent calls.');
    }
}
