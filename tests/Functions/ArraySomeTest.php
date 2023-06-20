<?php

namespace Tests\Functions;

use PHPUnit\Framework\TestCase;

class ArraySomeTest extends TestCase
{
    public function testEmptyArray()
    {
        $this->assertFalse(array_some([], fn($x) => true));
    }

    public function testDefaultPredicate()
    {
        $this->assertFalse(array_some(['', [], 0, null, false]));
        $this->assertTrue(array_some(['', [], 'teste', 1234]));
    }

    public function testCustomPredicate()
    {
        $a = [
            ['id' => 1, 'name' => 'João'],
            ['id' => 2, 'name' => 'José'],
            ['id' => 3, 'name' => 'Maria'],
        ];

        $this->assertFalse(array_some($a, fn($x) => $x['id'] == 10));
        $this->assertTrue(array_some($a, fn($x) => $x['id'] == 1));
        $this->assertTrue(array_some($a, fn($x) => $x['name'] == 'João'));
    }
}
