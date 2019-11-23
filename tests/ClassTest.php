<?php

namespace PhpEditor\Tests;

use PhpEditor\File;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPEditor\Class_
 */
class ClassTest extends TestCase
{
    public function testGetName()
    {
        $class = File::createFromSource('<?php class Foo {}')->getClass();
        $this->assertEquals('Foo', $class->getName());
    }

    public function testIsAbstract()
    {
        $class = File::createFromSource('<?php class Foo {}')->getClass();
        $this->assertFalse($class->isAbstract());

        $class = File::createFromSource('<?php abstract class Foo {}')->getClass();
        $this->assertTrue($class->isAbstract());
    }

    public function testIsInterface()
    {
        $class = File::createFromSource('<?php class Foo {}')->getClass();
        $this->assertFalse($class->isInterface());

        $class = File::createFromSource('<?php interface Foo {}')->getClass();
        $this->assertTrue($class->isInterface());
    }

    public function testIsClass()
    {
        $class = File::createFromSource('<?php class Foo {}')->getClass();
        $this->assertTrue($class->isClass());

        $class = File::createFromSource('<?php interface Foo {}')->getClass();
        $this->assertFalse($class->isClass());
    }

    public function testSetName()
    {
        $file = File::createFromSource('<?php class Foo {}');
        $class = $file->getClass();
        $class->setName('Bar');

        $this->assertEquals('<?php class Bar {}', $file->getSource());
    }

    public function testSetFinal()
    {
        $file = File::createFromSource('<?php class Foo {}');
        $class = $file->getClass();

        $class->setFinal();
        $this->assertEquals('<?php final class Foo {}', $file->getSource());

        $class->setFinal();
        $this->assertEquals('<?php final class Foo {}', $file->getSource());

        $class->setFinal(false);
        $this->assertEquals('<?php class Foo {}', $file->getSource());

        $class->setFinal(false);
        $this->assertEquals('<?php class Foo {}', $file->getSource());
    }

    public function testSetAbstract()
    {
        $file = File::createFromSource('<?php class Foo {}');
        $class = $file->getClass();

        $class->setAbstract();
        $this->assertEquals('<?php abstract class Foo {}', $file->getSource());

        $class->setAbstract();
        $this->assertEquals('<?php abstract class Foo {}', $file->getSource());

        $class->setAbstract(false);
        $this->assertEquals('<?php class Foo {}', $file->getSource());

        $class->setAbstract(false);
        $this->assertEquals('<?php class Foo {}', $file->getSource());
    }

    public function testGetExtendsEmpty()
    {
        $class = File::createFromSource('<?php class Foo {}')->getClass();
        $this->assertNull($class->getExtends());
    }

    public function testGetExtendsSomething()
    {
        $class = File::createFromSource('<?php class Foo extends Bar {}')->getClass();
        $this->assertEquals('Bar', $class->getExtends());
    }

    public function testSetExtends()
    {
        $file = File::createFromSource('<?php class Foo {}');
        $class = $file->getClass();

        $class->setExtends('Bar');
        $this->assertEquals('<?php class Foo extends Bar {}', $file->getSource());

        $class->setExtends('Baz');
        $this->assertEquals('<?php class Foo extends Baz {}', $file->getSource());
    }

    public function testRemoveExtends()
    {
        $file = File::createFromSource('<?php class Foo extends Bar {}');
        $class = $file->getClass();

        $class->removeExtends();
        $this->assertEquals('<?php class Foo {}', $file->getSource());

        $class->removeExtends();
        $this->assertEquals('<?php class Foo {}', $file->getSource());
    }

    public function testGetImplementsEmpty()
    {
        $class = File::createFromSource('<?php class Foo {}')->getClass();

        $this->assertEmpty($class->getImplements());
    }

    public function testGetImplementsNoExtends()
    {
        $class = File::createFromSource('<?php class Foo implements Bar {}')->getClass();

        $this->assertEquals(['Bar'], $class->getImplements());
    }

    public function testGetImplementsWithExtends()
    {
        $class = File::createFromSource('<?php class Foo extends Bar implements Baz {}')->getClass();
        $this->assertEquals(['Baz'], $class->getImplements());
    }

    public function testGetImplementsMultipleWithExtends()
    {
        $class = File::createFromSource('<?php class Foo extends Bar implements Baz1, Baz2 {}')->getClass();
        $this->assertEquals(['Baz1', 'Baz2'], $class->getImplements());
    }

    public function testHasImplements()
    {
        $class = File::createFromSource('<?php class Foo extends Bar implements Baz1, Baz2 {}')->getClass();

        $this->assertTrue($class->hasInImplements('Baz1'));
        $this->assertTrue($class->hasInImplements('Baz2'));
        $this->assertFalse($class->hasInImplements('Baz3'));
    }

    public function testSetImplements()
    {
        $file = File::createFromSource('<?php class Foo implements Bar {}');

        $file->getClass()->setImplements(['Baz']);

        $this->assertEquals('<?php class Foo implements Baz {}', $file->getSource());
    }

    public function testAddToImplementsEmpty()
    {
        $file = File::createFromSource('<?php class Foo {}');
        $class = $file->getClass();

        $class->addToImplements('Bar');

        $this->assertEquals('<?php class Foo implements Bar {}', $file->getSource());
    }

    public function testAddToImplementsExisting()
    {
        $file = File::createFromSource('<?php class Foo implements Bar {}');
        $class = $file->getClass();

        $class->addToImplements('Baz');

        $this->assertEquals('<?php class Foo implements Bar, Baz {}', $file->getSource());
    }

    public function testAddToImplementsAlreadyPresent()
    {
        $file = File::createFromSource('<?php class Foo implements Bar {}');
        $class = $file->getClass();

        $class->addToImplements('Bar');

        $this->assertEquals('<?php class Foo implements Bar {}', $file->getSource());
    }

    public function testAddToImplementsWithExtends()
    {
        $file = File::createFromSource('<?php class Foo extends Bar {}');
        $class = $file->getClass();

        $class->addToImplements('Baz');

        $this->assertEquals('<?php class Foo extends Bar implements Baz {}', $file->getSource());
    }

    public function testAddToImplementsBeginning()
    {
        $file = File::createFromSource('<?php class Foo implements Baz {}');
        $class = $file->getClass();

        $class->addToImplements('Bar');

        $this->assertEquals('<?php class Foo implements Bar, Baz {}', $file->getSource());
    }

    public function testAddToImplementsEnding()
    {
        $file = File::createFromSource('<?php class Foo implements Bar, Baz {}');
        $class = $file->getClass();

        $class->addToImplements('Demo');

        $this->assertEquals('<?php class Foo implements Bar, Baz, Demo {}', $file->getSource());
    }

    public function testRemoveFromImplementsBeginning()
    {
        $file = File::createFromSource('<?php class Foo implements Bar, Baz {}');
        $class = $file->getClass();

        $class->removeFromImplements('Bar');

        $this->assertEquals('<?php class Foo implements Baz {}', $file->getSource());
    }

    public function testRemoveFromImplementsEnding()
    {
        $file = File::createFromSource('<?php class Foo implements Bar, Baz {}');
        $class = $file->getClass();

        $class->removeFromImplements('Baz');

        $this->assertEquals('<?php class Foo implements Bar {}', $file->getSource());
    }

    public function testRemoveFromImplementsAbsent()
    {
        $file = File::createFromSource('<?php class Foo implements Bar {}');
        $class = $file->getClass();

        $class->removeFromImplements('Baz');

        $this->assertEquals('<?php class Foo implements Bar {}', $file->getSource());
    }

    public function testRemoveFromImplementsLast()
    {
        $file = File::createFromSource('<?php class Foo implements Bar {}');
        $class = $file->getClass();

        $class->removeFromImplements('Bar');

        $this->assertEquals('<?php class Foo {}', $file->getSource());
    }

    public function testRemoveFromImplementsWithExtends()
    {
        $file = File::createFromSource('<?php class Foo extends Bar implements Baz {}');
        $class = $file->getClass();

        $class->removeFromImplements('Baz');

        $this->assertEquals('<?php class Foo extends Bar {}', $file->getSource());
    }

    public function testGetFirstToken()
    {
        $file = File::createFromSource("<?php\n\nclass Foo {}");
        $class = $file->getClasses()->get('Foo');

        $this->assertEquals(2, $file->getTokens()->getTokenOffset($class->getFirstToken()));
    }

    public function testGetFirstTokenForAbstract()
    {
        $file = File::createFromSource("<?php\n\nabstract class Foo {}");
        $class = $file->getClasses()->get('Foo');

        $this->assertEquals(2, $file->getTokens()->getTokenOffset($class->getFirstToken()));
    }

    public function testGetLastToken()
    {
        $file = File::createFromSource("<?php\n\nclass Foo {}\n\n");
        $class = $file->getClasses()->get('Foo');

        $this->assertEquals(7, $file->getTokens()->getTokenOffset($class->getLastToken()));
    }

    public function testGetLastTokenForInvalid()
    {
        $file = File::createFromSource("<?php\n\nabstract class Foo {");
        $class = $file->getClasses()->get('Foo');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Number of opening/closing brackets for class "Foo" do not match.');

        $class->getLastToken();
    }

    public function testGetLastTokenForNoOpening()
    {
        $file = File::createFromSource("<?php\n\nabstract class Foo");
        $class = $file->getClasses()->get('Foo');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Found no opening bracket for class "Foo".');

        $class->getLastToken();
    }

    public function testGetLastTokenForComposedClass()
    {
        $file = File::createFromSource("<?php\n\nclass Foo { public function getBar() { return 'bar'; } }");
        $class = $file->getClasses()->get('Foo');
        $last = $class->getLastToken();

        $this->assertSame($last, $file->getTokens()->getLast());
    }
}
