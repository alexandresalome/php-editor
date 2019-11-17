<?php

namespace PhpEditor\Tests;

use PhpEditor\File;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpEditor\File
 * @covers \PhpEditor\Uses
 */
class UsesTest extends TestCase
{
    public function testGetAll()
    {
        $source = $this->getCodeWithUses([
            '\RuntimeException',
            'Acme\Model\Project as ProjectModel',
            'Acme\Service\Project as ProjectService',
        ]);

        $expected = [
            ['\RuntimeException', null],
            ['Acme\Model\Project', 'ProjectModel'],
            ['Acme\Service\Project', 'ProjectService'],
        ];

        $this->assertEquals($expected, File::createFromSource($source)->getUses()->getAll());
    }

    public function testHas(): void
    {
        $source = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz',
            'Baz as OtherBaz',
        ]);
        $uses = File::createFromSource($source)->getUses();

        $this->assertTrue($uses->has('Foo\Bar\Test'));
        $this->assertTrue($uses->has('Bar\Baz'));
        $this->assertTrue($uses->has('Baz'));
        $this->assertFalse($uses->has('Test'));
        $this->assertFalse($uses->has('Foo\Test'));
        $this->assertFalse($uses->has('OtherBaz'));
    }

    public function testRemove(): void
    {
        $source = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz',
            'Baz as OtherBaz',
        ]);
        $file = File::createFromSource($source);
        $uses = $file->getUses();

        $uses->remove('Bar\Baz');
        $expected = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Baz as OtherBaz',
        ]);

        $this->assertEquals($expected, $file->getSource());
    }

    public function testRemoveLast(): void
    {
        $source = $this->getCodeWithUses(['Foo']);
        $file = File::createFromSource($source);
        $uses = $file->getUses();

        $uses->remove('Foo');
        $expected = $this->getCodeWithUses([]);

        $this->assertEquals($expected, $file->getSource());
    }

    public function testRemoveWithAlias(): void
    {
        $source = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz',
            'Baz as OtherBaz',
        ]);
        $file = File::createFromSource($source);
        $uses = $file->getUses();

        $uses->remove('Baz');

        $expected = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz',
        ]);
        $this->assertEquals($expected, $file->getSource());
    }

    public function testGetAlias()
    {
        $source = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz',
            'Baz as OtherBaz',
        ]);
        $uses = File::createFromSource($source)->getUses();

        $this->assertNull($uses->getAlias('Foo\Bar\Test'));
        $this->assertEquals('OtherBaz', $uses->getAlias('Baz'));
    }

    public function testSetAlias()
    {
        $source = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz',
            'Baz as OtherBaz',
        ]);

        $file = File::createFromSource($source);
        $uses = $file->getUses();
        $uses->setAlias('Bar\Baz', 'FirstBaz');
        $uses->setAlias('Baz', 'NewBaz');
        $expected = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz as FirstBaz',
            'Baz as NewBaz',
        ]);

        $this->assertEquals($expected, $file->getSource());
    }

    public function testSetAliasOnNonExisting()
    {
        $source = $this->getCodeWithUses([
            'Foo',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File does not contain use for class "Bar"');

        File::createFromSource($source)->getUses()->setAlias('Bar', 'Baz');
    }

    public function testSetAliasRemoveAlias()
    {
        $source = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz as SomeAlias',
        ]);

        $file = File::createFromSource($source);
        $uses = $file->getUses();
        $uses->setAlias('Bar\Baz', null);
        $expected = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz',
        ]);

        $this->assertEquals($expected, $file->getSource());
    }

    public function testAliasNoClass()
    {
        $source = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz',
            'Baz as OtherBaz',
        ]);
        $uses = File::createFromSource($source)->getUses();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Class "Some" is not in use statements.');

        $uses->getAlias('Some');
    }

    public function testAdd(): void
    {
        $source = $this->getCodeWithUses([
            'Foo\Bar\Test',
            'Bar\Baz',
            'Baz as OtherBaz',
        ]);
        $file = File::createFromSource($source);
        $file->getUses()->add('Bar\Baz\SomeClass');
        $file->getUses()->add('Bar\Demo\SomeClass', 'SomeOtherClass');

        $expected = $this->getCodeWithUses([
            'Bar\Baz\SomeClass',
            'Bar\Demo\SomeClass as SomeOtherClass',
            'Foo\Bar\Test',
            'Bar\Baz',
            'Baz as OtherBaz',
        ]);
        $this->assertEquals($expected, $file->getSource());
    }

    public function testAddAlreadyPresent()
    {
        $source = $this->getCodeWithUses(['Foo']);
        $file = File::createFromSource($source);
        $file->getUses()->add('Foo');

        $expected = $this->getCodeWithUses([
            'Foo',
        ]);
        $this->assertEquals($expected, $file->getSource());
    }

    public function testAddAlias()
    {
        $source = $this->getCodeWithUses(['Foo']);
        $file = File::createFromSource($source);
        $file->getUses()->add('Foo', 'Bar');

        $expected = $this->getCodeWithUses([
            'Foo as Bar',
        ]);
        $this->assertEquals($expected, $file->getSource());
    }

    public function testAddOnNoUse()
    {
        $source = $this->getCodeWithUses([]);
        $file = File::createFromSource($source);
        $file->getUses()->add('Foo\Bar', 'Baz');
        $file->getUses()->add('Bar');

        $expected = $this->getCodeWithUses([
            'Bar',
            'Foo\Bar as Baz',
        ]);
        $this->assertEquals($expected, $file->getSource());
    }

    public function testAddOnEmpty()
    {
        $file = File::create();
        $file->getUses()->add('Bar');

        $expected = $this->getCodeWithUses([
            'Bar',
        ], false, false, false);
        $this->assertEquals($expected, $file->getSource());
    }

    private function getCodeWithUses(array $uses, bool $withNamespace = true, bool $withComment = true, bool $withClass = true): string
    {
        $source = "<?php\n";
        if ($withComment) {
            $source .= "/** This is a file comment */\n";
        }

        if ($withNamespace) {
            $source .= "\nnamespace Foo;\n";
        }

        if (count($uses)) {
            $source .= "\n";
            foreach ($uses as $value) {
                $source .= 'use '.$value.";\n";
            }
        }

        if ($withClass) {
            $source .= "\n/** Some doc */\nclass Demo {}\n";
        }

        return $source;
    }
}
