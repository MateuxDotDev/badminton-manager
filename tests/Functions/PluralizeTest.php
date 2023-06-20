<?php

namespace Tests\Functions;

use PHPUnit\Framework\TestCase;

class PluralizeTest extends TestCase
{
    public function testPluralizeSingular(): void
    {
        $expected = '1 item';

        $result = pluralize(1, 'item', 'items');

        $this->assertEquals($expected, $result);
    }

    public function testPluralizePlural(): void
    {
        $expected = '2 items';

        $result = pluralize(2, 'item', 'items');

        $this->assertEquals($expected, $result);
    }
}