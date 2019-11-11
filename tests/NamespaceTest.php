<?php

namespace PhpEditor\Tests;

use PhpEditor\File;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpEditor\File
 * @covers \PhpEditor\Namespace_
 */
class NamespaceTest extends TestCase
{
    public function testGetNamespace()
    {
        $file = File::createFromSource('<?php namespace Foo;');
        $this->assertEquals('Foo', $file->getNamespace());
    }

    public function testGetNamespaceFromEmptyFile()
    {
        $file = File::createFromSource('<?php ');
        $this->assertNull($file->getNamespace());
    }

    public function testSetNamespace()
    {
        $file = File::createFromSource('<?php namespace Foo;');
        $file->setNamespace('Bar\Baz');
        $this->assertEquals('<?php namespace Bar\Baz;', $file->getSource());
    }

    public function testSetNamespaceEmptyFile()
    {
        $file = File::create();
        $file->setNamespace('Bar\Baz');
        $this->assertEquals("<?php\n\nnamespace Bar\Baz;\n", $file->getSource());
    }

    public function testHasNamespace()
    {
        $file = File::createFromSource('<?php namespace Foo;');
        $this->assertTrue($file->hasNamespace());
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
        $this->assertFalse($file->hasNamespace());
    }

    public function testGetOrCreateOpening()
    {
        $file = File::createFromSource('<?php echo "Hello";');
        $token = $file->getOrCreateOpening();
        $this->assertEquals('<?php ', $token->getValue());
    }

    public function testGetOrCreateOpeningNotExisting()
    {
        $file = File::create();
        $token = $file->getOrCreateOpening();
        $this->assertEquals("<?php\n", $token->getValue());
    }
}
