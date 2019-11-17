<?php

namespace PhpEditor\Tests;

use PhpEditor\File;
use PhpEditor\Namespace_;
use PhpEditor\Uses;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpEditor\File
 */
class FileTest extends TestCase
{
    public function testCreate()
    {
        $file = File::create();
        $this->assertEquals('', $file->getSource());
    }

    public function testCreateFromSource()
    {
        $file = File::createFromSource('<?php echo "Hello";');
        $this->assertCount(5, $file->getTokens());
    }

    public function testCreateFromFile()
    {
        $filePath = tempnam(sys_get_temp_dir(), 'pm_');
        file_put_contents($filePath, '<?php echo "Hello";');

        try {
            $file = File::createFromFile($filePath);
        } finally {
            unlink($filePath);
        }

        $this->assertCount(5, $file->getTokens());
    }

    public function testSaveToFile()
    {
        $source = '<?php echo "Hello";';
        $filePath = tempnam(sys_get_temp_dir(), 'pm_');
        try {
            $file = File::createFromSource($source);
            $file->saveToFile($filePath);
            $this->assertStringEqualsFile($filePath, $source);
        } finally {
            unlink($filePath);
        }
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

    public function testMonolithicForLateOpening()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only monolithic files are supported (starting with <?php, no close tag).');

        $file = File::createFromSource('Hello <?php echo "world";');
    }

    public function testMonolithicForClosing()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only monolithic files are supported (starting with <?php, no close tag).');

        $file = File::createFromSource('Hello <?php echo "world"; ?>');
    }

    public function testGetNamespace()
    {
        $file = File::create();
        $this->assertInstanceOf(Namespace_::class, $file->getNamespace());
    }

    public function testGetUses()
    {
        $file = File::create();
        $this->assertInstanceOf(Uses::class, $file->getUses());
    }
}
