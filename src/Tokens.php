<?php

namespace PhpEditor;

use Traversable;

/**
 * Represents a collection of tokens.
 *
 * @internal
 */
class Tokens implements \Countable, \ArrayAccess, \IteratorAggregate
{
    /**
     * @var Token[]
     */
    private $tokens = [];

    /**
     * Tokens constructor.
     *
     * @param array $tokens a list of PHP tokens
     */
    public function __construct(array $tokens = [])
    {
        /** @var Token $previous */
        $previous = null;
        foreach ($tokens as $token) {
            $token = Token::createFromValue($token);
            $this->tokens[] = $token;

            if ($previous) {
                $previous->setNext($token);
                $token->setPrevious($previous);
            }

            $previous = $token;
        }
    }

    /**
     * Creates a token list from a PHP file.
     *
     * @param string $file path to a PHP file
     *
     * @return Tokens a list of tokens
     */
    public static function createFromFile(string $file): Tokens
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist.', $file));
        }

        return self::createFromSource(file_get_contents($file));
    }

    /**
     * Creates a token list from source string.
     *
     * @param string $source  a string representing PHP source code
     * @param bool   $withTag indicates if the code contains PHP tags or is pure PHP
     *
     * @return Tokens a list of tokens
     */
    public static function createFromSource(string $source, $withTag = true): Tokens
    {
        if ($withTag) {
            $tokens = token_get_all($source);
        } else {
            $tokens = token_get_all('<?php '.$source);
            array_shift($tokens);
        }

        return new Tokens($tokens);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->tokens);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        $this->validateOffset($offset);

        return isset($this->tokens[$offset]);
    }

    /**
     * Fetches a token at given offset in list.
     *
     * @param int $offset a token offset
     *
     * @return Token a token
     */
    public function offsetGet($offset): Token
    {
        $this->validateOffset($offset, true);

        return $this->tokens[$offset];
    }

    /**
     * Changes a token in the list.
     *
     * @param int a token offset
     * @param Token $value the new offset to replace with
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof Token) {
            throw new \InvalidArgumentException(sprintf('Expected a "%s", got a "%s".', Token::class, is_object($value) ? get_class($value) : strtolower(gettype($value))));
        }

        $this->validateOffset($offset);
        if ($offset > count($this->tokens)) {
            throw new \InvalidArgumentException(sprintf('Offset "%d" is out of range (min: 0, max: %d).', $offset, count($this->tokens)));
        }

        $this->tokens[$offset] = $value;
        $previous = $this->tokens[$offset - 1] ?? null;
        $next = $this->tokens[$offset + 1] ?? null;

        $value->setPrevious($previous);
        if ($previous) {
            $previous->setNext($value);
        }

        $value->setNext($next);
        if ($next) {
            $next->setPrevious($value);
        }
    }

    /**
     * Removes a token from the list.
     *
     * @param int a token offset
     */
    public function offsetUnset($offset): void
    {
        $this->validateOffset($offset, true);
        $pruned = $this->tokens[$offset];
        unset($this->tokens[$offset]);
        $this->tokens = array_values($this->tokens);
        $previous = $pruned->getPrevious();
        $next = $pruned->getNext();

        if ($previous) {
            $previous->setNext($next);
        }

        if ($next) {
            $next->setPrevious($previous);
        }
    }

    /**
     * Returns an iterator over every tokens.
     *
     * @return Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->tokens);
    }

    /**
     * Returns source code from token list.
     *
     * @return string a PHP source string
     */
    public function getSource(): string
    {
        return implode('', array_map(function (Token $token) {
            return $token->getValue();
        }, $this->tokens));
    }

    /**
     * Returns the first token of the list, or null if empty.
     */
    public function getFirst(): ?Token
    {
        return $this->tokens[0] ?? null;
    }

    /**
     * Returns the last token of the list, or null if empty.
     */
    public function getLast(): ?Token
    {
        return $this->tokens[count($this->tokens) - 1] ?? null;
    }

    /**
     * Returns the offset of a given token.
     *
     * @param Token $token a token to search
     *
     * @return int token offset
     *
     * @throws \InvalidArgumentException token not found in list
     */
    public function getTokenOffset(Token $token): int
    {
        $search = array_search($token, $this->tokens, true);
        if (false === $search) {
            throw new \InvalidArgumentException('Token not present in the token list.');
        }

        return $search;
    }

    public function push(Token $token)
    {
        $this[count($this->tokens)] = $token;
    }

    /**
     * Returns all tokens of a given type.
     *
     * @return Token[]
     */
    public function getAllByType(int $type): array
    {
        return array_values(array_filter($this->tokens, function (Token $token) use ($type) {
            return $token->getType() === $type;
        }));
    }

    /**
     * Inserts PHP code (without opening tag) after a given token.
     */
    public function insertCodeAfter(Token $token, string $string): void
    {
        $tokens = Tokens::createFromSource($string, false);
        $this->insertTokensAfter($token, $tokens);
    }

    /**
     * Inserts a list of tokens after a specific token.
     */
    public function insertTokensAfter(Token $token, Tokens $tokens): void
    {
        $offset = $this->getTokenOffset($token);
        $this->insertTokensAt($offset + 1, $tokens);
    }

    public function insertTokensAt(int $offset, Tokens $tokens): void
    {
        if ($offset > count($this->tokens)) {
            throw new \InvalidArgumentException(sprintf('Offset "%d" is out of range (min: 0, max: %d).', $offset, count($this->tokens)));
        }

        if (0 === count($tokens)) {
            return;
        }

        $tokens = iterator_to_array($tokens);

        $begin = $this[$offset - 1] ?? null;
        $end = $this[$offset] ?? null;
        $innerBegin = reset($tokens);
        $innerEnd = end($tokens);

        $innerBegin->setPrevious($begin);
        $innerEnd->setNext($end);

        if ($begin) {
            $begin->setNext($innerBegin);
        }
        if ($end) {
            $end->setPrevious($innerEnd);
        }

        $this->tokens = array_merge(
            array_slice($this->tokens, 0, $offset),
            array_values($tokens),
            array_values(array_slice($this->tokens, $offset))
        );
    }

    /**
     * Transforms the token list to a string representation.
     */
    public function __toString(): string
    {
        $output = sprintf("| Offset | %-26s | Value\n", 'Name');
        $output .= sprintf("| ------ | %-26s | -----\n", str_repeat('-', 26));
        foreach ($this->tokens as $offset => $token) {
            $output .= sprintf(
                "| %-6d | %-26s | %s%%\n",
                $offset,
                $token->getTypeName(),
                implode("\n".str_repeat(' ', 40), explode("\n", $token->getValue())));
        }

        return $output;
    }

    private function validateOffset($offset, bool $exists = false): void
    {
        if (!is_int($offset)) {
            throw new \InvalidArgumentException(sprintf('Expected an integer as offset, got a "%s".', strtolower(gettype($offset))));
        }

        if (!$exists) {
            return;
        }

        if (!isset($this->tokens[$offset])) {
            throw new \InvalidArgumentException(sprintf('Offset "%d" is not defined.', $offset));
        }
    }
}
