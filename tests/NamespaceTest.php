<?php

namespace PhpEditor\Tests;

use PhpEditor\File;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpEditor\Namespace_
 */
class NamespaceTest extends TestCase
{
    public function testGetNamespace()
    {
        $file = File::createFromSource('<?php namespace Foo;');
        $this->assertEquals('Foo', $file->getNamespace()->get());
    }

    public function testGetNamespaceFromEmptyFile()
    {
        $file = File::createFromSource('<?php ');
        $this->assertNull($file->getNamespace()->get());
    }

    public function testSetNamespace()
    {
        $file = File::createFromSource('<?php namespace Foo;');
        $file->getNamespace()->set('Bar\Baz');
        $this->assertEquals('<?php namespace Bar\Baz;', $file->getSource());
    }

    public function testSetNamespaceEmptyFile()
    {
        $file = File::create();
        $file->getNamespace()->set('Bar\Baz');
        $this->assertEquals("<?php\n\nnamespace Bar\Baz;\n", $file->getSource());
    }

    public function testHasNamespace()
    {
        $file = File::createFromSource('<?php namespace Foo;');
        $this->assertTrue($file->getNamespace()->exists());
    }

    public function testNoMultipleNamespaces()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only files with zero or one namespace are supported.');

        File::createFromSource('<?php namespace Foo; namespace Bar;');
    }

    public function testHasNamespaceNotDefined()
    {
        $file = File::createFromSource('<?php ');
        $this->assertFalse($file->getNamespace()->exists());
    }
}
