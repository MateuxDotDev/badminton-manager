<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ArrayEveryTest extends TestCase
{
    public function testEmptyArray()
    {
        // vacuously true
        // every element is true is logically the same as
        // there's no element that is not true, because
        // there's simply no element
        $this->assertTrue(array_every([], fn($x) => true));
    }

    public function testDefaultPredicate()
    {
        $this->assertFalse(array_every([10, 20, 30, 0, '']));
        $this->assertTrue(array_every([10, 20, 30, 40, 'teste']));
    }

    public function testCustomPredicate()
    {
        $a = [
            ['id' => 1, 'name' => 'João'],
            ['id' => 2, 'name' => 'José'],
            ['id' => 3, 'name' => 'Maria'],
        ];

        $this->assertFalse(array_every($a, fn($x) => $x['id'] % 2 == 0));
        $this->assertTrue(array_every($a, fn($x) => $x['id'] < 5));
    }
}
