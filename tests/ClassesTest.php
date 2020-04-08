<?php

namespace PhpEditor\Tests;

use PhpEditor\Classes;
use PhpEditor\File;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPEditor\Classes
 */
class ClassesTest extends TestCase
{
    public function testGetAll()
    {
        $file = File::createFromSource("<?php\n\nclass Foo {}\nclass Bar {}\nclass Baz {}\n");
        $classes = (new Classes($file))->getAll();
        $this->assertEquals('Foo', $classes[0]->getName());
        $this->assertEquals('Bar', $classes[1]->getName());
        $this->assertEquals('Baz', $classes[2]->getName());
    }

    public function testCount()
    {
        $file = File::createFromSource("<?php\n\nclass Foo {}\nclass Bar {}\nclass Baz {}\n");
        $classes = new Classes($file);

        $this->assertCount(3, $classes);
    }

    public function testIterator()
    {
        $file = File::createFromSource("<?php\n\nclass Foo {}\nclass Bar {}\nclass Baz {}\n");
        $classes = iterator_to_array(new Classes($file));

        $this->assertCount(3, $classes);
        $this->assertEquals('Foo', $classes[0]->getName());
        $this->assertEquals('Bar', $classes[1]->getName());
        $this->assertEquals('Baz', $classes[2]->getName());
    }

    public function testGetExisting()
    {
        $file = File::createFromSource("<?php\n\nclass Foo {}\nclass Bar {}\nclass Baz {}\n");

        $foo = $file->getClasses()->get('Foo');
        $this->assertEquals($foo->getName(), 'Foo');

        $foo = $file->getClasses()->get('Bar');
        $this->assertEquals($foo->getName(), 'Bar');
    }

    public function testGetNotExisting()
    {
        $file = File::createFromSource("<?php\n\nclass Foo {}\nclass Bar {}\nclass Baz {}\n");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Found no class named "NotExisting" in the given file.');
        $file->getClasses()->get('NotExisting');
    }

    public function testCreate()
    {
        $file = File::createFromSource("<?php\n\nclass Foo {}\n");
        $file->getClasses()->create('Bar');
        $expected = "<?php\n\nclass Foo {}\n\nclass Bar\n{\n}\n";

        $this->assertEquals($expected, $file->getSource());
    }

    public function testCreateAlreadyExisting()
    {
        $file = File::createFromSource("<?php\n\nclass Foo {}\n");

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The file already contains a class "Foo".');

        $file->getClasses()->create('Foo');
    }

    public function testRemove()
    {
        $file = File::createFromSource("<?php\n\nclass Foo {}");

        $file->getClasses()->remove('Foo');

        $this->assertEquals("<?php\n", $file->getSource());
    }

    public function testRemoveWithNamespace()
    {
        $file = File::createFromSource("<?php\n\nnamespace Acme\Model;\n\nclass Foo {}\n");

        $file->getClasses()->remove('Foo');

        $this->assertEquals("<?php\n\nnamespace Acme\Model;\n", $file->getSource());
    }
}
