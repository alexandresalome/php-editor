<?php

namespace PhpEditor\Tests;

use PhpEditor\Token;
use PhpEditor\Tokens;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpEditor\Tokens
 */
class TokensTest extends TestCase
{
    public function testCreateFromFile()
    {
        $source = '<?php echo "Hello world";';
        $tmpFile = tempnam(sys_get_temp_dir(), 'pm_');

        file_put_contents($tmpFile, $source);

        try {
            $tokens = Tokens::createFromFile($tmpFile);
        } finally {
            unlink($tmpFile);
        }

        $this->assertCount(5, $tokens);
        $this->assertEquals($source, $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testCreateFromNotExistingFile()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'pm_');
        unlink($tmpFile);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("File \"$tmpFile\" does not exist.");

        Tokens::createFromFile($tmpFile);
    }

    public function testCreateFromSourceWithPhpTag()
    {
        $source = '<?php "ok"';
        $tokens = Tokens::createFromSource($source);
        $this->assertCount(2, $tokens);
        $this->assertEquals($source, $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testCreateFromSourceWithoutTag()
    {
        $source = '"ok"." Bobby"';
        $tokens = Tokens::createFromSource($source, false);
        $this->assertCount(3, $tokens);
        $this->assertEquals(T_CONSTANT_ENCAPSED_STRING, $tokens[0]->getType());
        $this->assertEquals($source, $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testEmptyWithoutTag()
    {
        $tokens = Tokens::createFromSource('', false);
        $this->assertCount(0, $tokens);
    }

    public function testEmptyWithTag()
    {
        $tokens = Tokens::createFromSource('');
        $this->assertCount(0, $tokens);
    }

    public function testArrayAccessIsset()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');

        // Exists
        $this->assertTrue(isset($tokens[4]));
        $this->assertFalse(isset($tokens[5]));
    }

    public function testArrayAccessGetExisting()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');
        $this->assertEquals(Token::TYPE_ENDING_SEMICOLON, $tokens[4]->getType());
    }

    public function testArrayAccessGetUndefined()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Offset "5" is not defined.');
        $tokens[5];
    }

    public function testArrayAccessGetString()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an integer as offset, got a "string".');
        $tokens['foo'];
    }

    public function testArrayAccessInvalidOffset()
    {
        $tokens = Tokens::createFromSource('');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an integer as offset, got a "string".');

        $tokens['foo'];
    }

    public function testArrayAccessSetExisting()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');
        $tokens[3] = new Token(T_CONSTANT_ENCAPSED_STRING, '"Hello you"');
        $this->assertEquals('"Hello you"', $tokens[3]->getValue());
        $this->assertChained($tokens);
    }

    public function testArrayAccessSetNewEnding()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');
        $tokens[5] = new Token(T_CONSTANT_ENCAPSED_STRING, '"foo"');
        $this->assertEquals('"foo"', $tokens[5]->getValue());
        $this->assertChained($tokens);
    }

    public function testArrayAccessSetOutOfRange()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Offset "6" is out of range (min: 0, max: 5).');

        $tokens[6] = new Token(T_CONSTANT_ENCAPSED_STRING, '"foo"');
    }

    public function testArrayAccessSetInvalidType()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a "PhpEditor\Token", got a "boolean".');

        $tokens[6] = true;
    }

    public function testArrayAccessInvalidValue()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a "PhpEditor\Token", got a "string".');

        $tokens[1] = 'foo';
    }

    public function testArrayAccessUnsetMiddle()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');

        unset($tokens[3]);
        $this->assertCount(4, $tokens);
        $this->assertEquals(Token::TYPE_ENDING_SEMICOLON, $tokens[3]->getType());
        $this->assertSame($tokens[2], $tokens[3]->getPrevious());
        $this->assertSame($tokens[3], $tokens[2]->getNext());
    }

    public function testArrayAccessUnsetBegin()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');

        unset($tokens[0]);
        $this->assertCount(4, $tokens);
        $this->assertEquals(T_ECHO, $tokens[0]->getType());
        $this->assertChained($tokens);
    }

    public function testArrayAccessUnsetEnd()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');
        unset($tokens[4]);
        $this->assertCount(4, $tokens);
        $this->assertEquals(T_CONSTANT_ENCAPSED_STRING, $tokens[3]->getType());
        $this->assertChained($tokens);
    }

    public function testGetFirst()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');
        $this->assertEquals('<?php ', $tokens->getFirst()->getValue());
    }

    public function testGetFirstWithEmpty()
    {
        $tokens = new Tokens();
        $this->assertNull($tokens->getFirst());
    }

    public function testGetLast()
    {
        $tokens = Tokens::createFromSource('<?php echo "Hello world";');
        $this->assertEquals(';', $tokens->getLast()->getValue());
    }

    public function testGetLastWithEmpty()
    {
        $tokens = new Tokens();
        $this->assertNull($tokens->getLast());
    }

    public function testGetSource()
    {
        $tokens = Tokens::createFromSource('<?php "foo";');
        $tokens[1] = new Token(T_CONSTANT_ENCAPSED_STRING, '"bar"');

        $this->assertEquals('<?php "bar";', $tokens->getSource());
    }

    public function testGetAllByType()
    {
        $tokens = Tokens::createFromSource('<?php echo "foo"; echo "bar";');
        $texts = $tokens->getAllByType(T_CONSTANT_ENCAPSED_STRING);

        $this->assertCount(2, $texts);
        $this->assertInstanceOf(Token::class, $texts[0]);
        $this->assertInstanceOf(Token::class, $texts[1]);

        $this->assertEquals('"foo"', $texts[0]->getValue());
        $this->assertEquals('"bar"', $texts[1]->getValue());
    }

    public function testInsertCodeAfter()
    {
        $tokens = Tokens::createFromSource('<?php echo "foo";');
        $foo = $tokens[3];
        $tokens->insertCodeAfter($foo, '."bar"');

        $this->assertCount(7, $tokens);
        $this->assertEquals('<?php echo "foo"."bar";', $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testInsertCodeBefore()
    {
        $tokens = Tokens::createFromSource('<?php echo "foo";');
        $foo = $tokens[3];
        $tokens->insertCodeBefore($foo, '"bar".');

        $this->assertCount(7, $tokens);
        $this->assertEquals('<?php echo "bar"."foo";', $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testGetTokenOffsetExisting()
    {
        $tokens = Tokens::createFromSource('<?php echo "foo";');
        $this->assertEquals(0, $tokens->getTokenOffset($tokens[0]));
        $this->assertEquals(2, $tokens->getTokenOffset($tokens[2]));
        $this->assertEquals(4, $tokens->getTokenOffset($tokens[4]));
    }

    public function testGetTokenOffsetNotExisting()
    {
        $tokens = Tokens::createFromSource('<?php echo "foo";');
        $token = new Token(T_ECHO, 'echo');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token not present in the token list.');

        $tokens->getTokenOffset($token);
    }

    public function testPush()
    {
        $tokens = new Tokens();
        $tokens->push(new Token(T_ECHO, 'echo'));
        $tokens->push(new Token(T_WHITESPACE, ' '));
        $tokens->push(new Token(T_CONSTANT_ENCAPSED_STRING, '"Foo"'));

        $this->assertEquals('echo "Foo"', $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testRemoveIntervalMiddle()
    {
        $tokens = Tokens::createFromSource('echo "Hello "."world";', false);
        $tokens->removeInterval($tokens[3], $tokens[4]);
        $this->assertEquals('echo "Hello ";', $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testRemoveIntervalBegin()
    {
        $tokens = Tokens::createFromSource('echo "Hello "."world";', false);
        $tokens->removeInterval($tokens[0], $tokens[3]);
        $this->assertEquals('"world";', $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testRemoveIntervalEnd()
    {
        $tokens = Tokens::createFromSource('echo "Hello "."world";', false);
        $tokens->removeInterval($tokens[3], $tokens[5]);
        $this->assertEquals('echo "Hello "', $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testInsertTokensAtMiddle()
    {
        $tokens = Tokens::createFromSource('echo "Hello ";', false);
        $toInsert = Tokens::createFromSource('."world"', false);

        $tokens->insertTokensAt(3, $toInsert);
        $this->assertEquals('echo "Hello "."world";', $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testInsertTokensAtBegin()
    {
        $tokens = Tokens::createFromSource('echo "Hello ";', false);
        $toInsert = Tokens::createFromSource('true && ', false);

        $tokens->insertTokensAt(0, $toInsert);
        $this->assertEquals('true && echo "Hello ";', $tokens->getSource());

        $this->assertChained($tokens);
    }

    public function testInsertTokensAtEnd()
    {
        $tokens = Tokens::createFromSource('echo "Hello ";', false);
        $toInsert = Tokens::createFromSource('echo "world";', false);

        $tokens->insertTokensAt(4, $toInsert);
        $this->assertEquals('echo "Hello ";echo "world";', $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testInsertTokensAtEmptyTokens()
    {
        $tokens = new Tokens();
        $toInsert = Tokens::createFromSource('echo "world";', false);

        $tokens->insertTokensAt(0, $toInsert);
        $this->assertEquals('echo "world";', $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testInsertTokensAtEmptyInsert()
    {
        $tokens = Tokens::createFromSource('echo "Hello ";', false);
        $toInsert = new Tokens();

        $tokens->insertTokensAt(0, $toInsert);
        $this->assertEquals('echo "Hello ";', $tokens->getSource());
        $this->assertChained($tokens);
    }

    public function testInsertTokensAtInvalidOffset()
    {
        $tokens = Tokens::createFromSource('echo "Hello ";', false);
        $toInsert = Tokens::createFromSource('."world"');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Offset "5" is out of range (min: 0, max: 4).');

        $tokens->insertTokensAt(5, $toInsert);
    }

    public function testToString()
    {
        $tokens = Tokens::createFromSource("echo \"Hello\nworld\";", false);
        $expected = <<<EXPECTED
| Offset | Name                       | Value
| ------ | -------------------------- | -----
| 0      | T_ECHO                     | echo%
| 1      | T_WHITESPACE               |  %
| 2      | T_CONSTANT_ENCAPSED_STRING | "Hello
                                        world"%
| 3      | TYPE_ENDING_SEMICOLON      | ;%

EXPECTED;
        $this->assertEquals($expected, (string) $tokens);
    }

    /**
     * @param ?Token[] $tokens
     */
    private function assertChained(Tokens $tokens)
    {
        /** @var ?Token $previous */
        $previous = null;
        $previousPosition = null;
        foreach ($tokens as $position => $token) {
            // If previous is a token and current is a token
            if ($previous instanceof Token && $token instanceof Token) {
                $this->assertSame($previous, $token->getPrevious(), sprintf('Previous of token at position %d is correct.', $position));
                $this->assertSame($token, $previous->getNext(), sprintf('Next of token at position %d is correct.', $previousPosition));

            // If previous is a token, but current is null
            } elseif ($previous instanceof Token) {
                $this->assertNull($previous->getNext(), sprintf('Next of token at position %d is correct.', $previousPosition));
            // If previous is null, but current is a token
            } elseif ($token instanceof Token) {
                $this->assertNull($token->getPrevious(), sprintf('Previous of token at position %d is correct', $position));
            }

            $previous = $token;
            $previousPosition = $position;
        }

        if ($previous instanceof Token) {
            $this->assertNull($previous->getNext(), sprintf('Next of token at position %d is correct.', $previousPosition));
        }
    }
}
