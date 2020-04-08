<?php

namespace PhpEditor\Tests;

use PhpEditor\Token;
use PhpEditor\Tokens;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpEditor\Token
 */
class TokenTest extends TestCase
{
    /**
     * @dataProvider getCreateFromValueData
     */
    public function testCreateFromValue($raw, $type, $value, ?string $exceptionClass = null, ?string $exceptionMessage = null)
    {
        if (null !== $exceptionClass) {
            $this->expectException($exceptionClass);
        }

        if (null != $exceptionMessage) {
            $this->expectExceptionMessage($exceptionMessage);
        }

        $token = Token::createFromValue($raw);
        $this->assertEquals($token->getType(), $type);
        $this->assertEquals($token->getValue(), $value);
    }

    public function getCreateFromValueData()
    {
        yield [';', Token::TYPE_ENDING_SEMICOLON, ';'];

        yield [[T_AS, 'as', 1], T_AS, 'as'];

        yield [true, null, null, \InvalidArgumentException::class, 'Expected a string or an array, got a "boolean".'];

        yield ['as', null, null, \InvalidArgumentException::class, 'Unable to create token from string "as".'];

        yield [['foo' => 'bar'], null, null, \InvalidArgumentException::class, 'Expected an array with 3 values, indexed 0, 1, 2, got something else.'];
    }

    public function testGetTypeNameWithStandardToken()
    {
        $token = new Token(T_ECHO, 'echo');
        $this->assertEquals('T_ECHO', $token->getTypeName());
    }

    public function testGetTypeNameWithCustomToken()
    {
        $token = new Token(Token::TYPE_CONCAT, '.');
        $this->assertEquals('TYPE_CONCAT', $token->getTypeName());
    }

    public function testGetTypeNameFromInteger()
    {
        $this->assertEquals('T_ECHO', Token::getTypeNameFromInteger(T_ECHO));
        $this->assertEquals('T_ECHO, or T_NS_SEPARATOR', Token::getTypeNameFromInteger([T_ECHO, T_NS_SEPARATOR]));
    }

    public function testSetValue()
    {
        $token = new Token(T_STRING, 'Foo');
        $token->setValue('Bar');
        $this->assertEquals('Bar', $token->getValue());
    }

    public function testPrevious()
    {
        $token = new Token(T_STRING, 'Foo');
        $previous = new Token(T_STRING, 'Bar');

        $this->assertNull($token->getPrevious());
        $token->setPrevious($previous);
        $this->assertSame($previous, $token->getPrevious());
    }

    public function testNext()
    {
        $token = new Token(T_STRING, 'Foo');
        $next = new Token(T_STRING, 'Bar');

        $this->assertNull($token->getNext());
        $token->setNext($next);
        $this->assertSame($next, $token->getNext());
    }

    public function testGetPrevious()
    {
        $token = new Token(T_STRING, 'Bar');
        $previous = new Token(T_STRING, 'Foo');
        $token->setPrevious($previous);

        $this->assertSame($previous, $token->getPrevious());
        $this->assertSame($previous, $token->getPrevious(T_STRING));
        $this->assertSame($previous, $token->getPrevious([T_ECHO, T_STRING]));
    }

    public function testGetPreviousTypeEndOfFile()
    {
        $token = new Token(T_STRING, 'Foo');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected token type to be T_ECHO, got end of file.');

        $token->getPrevious(T_ECHO);
    }

    public function testGetPreviousInvalidType()
    {
        $token = new Token(T_STRING, 'Bar');
        $previous = new Token(T_STRING, 'Foo');
        $token->setPrevious($previous);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected token type to be T_ECHO, got T_STRING.');

        $token->getPrevious(T_ECHO);
    }

    public function testGetNextWithCorrectType()
    {
        $token = new Token(T_STRING, 'Foo');
        $next = new Token(T_STRING, 'Bar');

        $token->setNext($next);
        $actual = $token->getNext(T_STRING);
        $this->assertSame($next, $actual);
    }

    public function testGetNextWithIncorrectType()
    {
        $token = new Token(T_STRING, 'Foo');
        $next = new Token(T_STRING, 'Bar');

        $token->setNext($next);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected token type to be T_ECHO, got T_STRING.');

        $actual = $token->getNext(T_ECHO);
    }

    public function testGetNextWithTypeOnEndOfFile()
    {
        $token = new Token(T_STRING, 'Foo');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected token type to be T_ECHO, got end of file.');

        $actual = $token->getNext(T_ECHO);
    }

    public function testIsNext()
    {
        $token = new Token(T_STRING, 'Foo');
        $next = new Token(T_STRING, 'Bar');
        $token->setNext($next);

        $this->assertTrue($token->isNext(T_STRING));
        $this->assertTrue($token->isNext([T_STRING, T_USE]));
        $this->assertFalse($token->isNext(T_ECHO));
        $this->assertFalse($token->isNext([T_ECHO, T_USE]));
    }

    public function testIsNextWithNoNext()
    {
        $token = new Token(T_STRING, 'Foo');

        $this->assertFalse($token->isNext(T_STRING));
        $this->assertFalse($token->isNext([T_ECHO, T_USE]));
    }

    public function testCustomTypes()
    {
        $refl = new \ReflectionClass(Token::class);

        foreach (array_keys($refl->getConstants()) as $name) {
            if (0 === strpos($name, 'TYPE_')) {
                $constant = constant(Token::class.'::'.$name);
                $this->assertArrayHasKey($constant, Token::NAME_OF_TYPES);
                $this->assertArrayHasKey($constant, Token::VALUE_OF_TYPES);
            }
        }
    }

    public function testGetPreviousNotEmpty()
    {
        $tokens = Tokens::createFromSource("<?php /** hello */\n\n/** world */     \n    class Foo {}");
        $class = $tokens[5];

        $previous = $class->getPreviousNotEmpty();
        $this->assertInstanceOf(Token::class, $previous);
        $this->assertEquals(T_OPEN_TAG, $previous->getType());
    }

    public function testGetPreviousNotEmptyInvalidType()
    {
        $tokens = Tokens::createFromSource("<?php /** hello */\n\n/** world */     \n    class Foo {}");
        $class = $tokens[5];

        $previous = $class->getPreviousNotEmpty(T_OPEN_TAG);
        $this->assertInstanceOf(Token::class, $previous);
        $this->assertEquals(T_OPEN_TAG, $previous->getType());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected token type to be T_ABSTRACT, got T_OPEN_TAG.');
        $class->getPreviousNotEmpty(T_ABSTRACT);
    }

    public function testGetPreviousBeginningOfFile()
    {
        $tokens = Tokens::createFromSource("/** hello */\n\n/** world */     \n    class Foo {}", false);
        $class = $tokens[4];

        $previous = $class->getPreviousNotEmpty();
        $this->assertNull($previous);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected token type to be T_ECHO, got beginning of file.');
        $class->getPreviousNotEmpty(T_ECHO);
    }

    public function testGetNextNotEmpty()
    {
        $tokens = Tokens::createFromSource("<?php /** hello */\n\n/** world */     \n    class Foo {}");
        $begin = $tokens[0];

        $next = $begin->getNextNotEmpty();
        $this->assertInstanceOf(Token::class, $next);
        $this->assertEquals(T_CLASS, $next->getType());
    }

    public function testGetNextNotEmptyInvalidType()
    {
        $tokens = Tokens::createFromSource("<?php /** hello */\n\n/** world */     \n    class Foo {}");
        $begin = $tokens[0];

        $next = $begin->getNextNotEmpty(T_CLASS);
        $this->assertInstanceOf(Token::class, $next);
        $this->assertEquals(T_CLASS, $next->getType());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected token type to be T_ABSTRACT, got T_CLASS.');
        $begin->getNextNotEmpty(T_ABSTRACT);
    }

    public function testGetNextEndOfFile()
    {
        $tokens = Tokens::createFromSource("/** hello */\n\n/** world */     \n    ", false);
        $begin = $tokens[0];

        $next = $begin->getNextNotEmpty();
        $this->assertNull($next);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected token type to be T_ECHO, got end of file.');
        $begin->getNextNotEmpty(T_ECHO);
    }
}
