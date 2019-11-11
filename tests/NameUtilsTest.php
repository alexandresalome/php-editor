<?php

namespace PhpEditor\Tests;

use PhpEditor\NameUtils;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpEditor\NameUtils
 */
class NameUtilsTest extends TestCase
{
    /**
     * @dataProvider getIsNamespacedData
     */
    public function testIsNamespaced(string $className, bool $expected)
    {
        $actual = NameUtils::isNamespaced($className);
        $this->assertEquals($expected, $actual);
    }

    public function getIsNamespacedData()
    {
        yield ['Foo', false];
        yield ['Foo\Bar', true];
        yield ['\Foo', true];
    }
}
