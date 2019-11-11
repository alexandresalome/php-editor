<?php

namespace PhpEditor\Tests;

use PhpEditor\Token;
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
}
