<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ArrayIndexByTest extends TestCase
{
    public function testIndexByStringKey()
    {
        $input = [
            ['id' => '1', 'name' => 'foo'],
            ['id' => '2', 'name' => 'bar'],
            ['id' => '3', 'name' => 'baz'],
        ];

        $expected = [
            '1' => ['id' => '1', 'name' => 'foo'],
            '2' => ['id' => '2', 'name' => 'bar'],
            '3' => ['id' => '3', 'name' => 'baz'],
        ];

        $result = array_index_by($input, 'id');

        $this->assertSame($expected, $result);
    }

    public function testIndexByNumericKey()
    {
        $input = [
            [100, 'foo'],
            [200, 'bar'],
            [300, 'baz'],
        ];

        $expected = [
            100 => [100, 'foo'],
            200 => [200, 'bar'],
            300 => [300, 'baz'],
        ];

        $result = array_index_by($input, 0);

        $this->assertSame($expected, $result);
    }

    public function testIndexByMissingKey()
    {
        $input = [
            ['id' => '1', 'name' => 'foo'],
            ['id' => '2', 'name' => 'bar'],
            ['id' => '3', 'name' => 'baz'],
        ];

        $expected = [];

        $result = array_index_by($input, 'missing_key');

        $this->assertSame($expected, $result);
    }
}
