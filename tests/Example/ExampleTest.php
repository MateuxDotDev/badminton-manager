<?php

namespace App\Tests\Example;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testAdd()
    {
        $a = 1;
        $b = 2;

        $sum = $a + $b;

        $this->assertEquals(3, $sum);
    }
}
