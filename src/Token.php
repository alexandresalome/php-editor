<?php

namespace PhpEditor;

/**
 * PHP token representation.
 *
 * @internal
 */
class Token
{
    const NAME_OF_TYPES = [
        self::TYPE_CONCAT => 'TYPE_CONCAT',
        self::TYPE_ENDING_SEMICOLON => 'TYPE_ENDING_SEMICOLON',
    ];

    const VALUE_OF_TYPES = [
        self::TYPE_CONCAT => '.',
        self::TYPE_ENDING_SEMICOLON => ';',
    ];

    const TYPE_CONCAT = 1000;
    const TYPE_ENDING_SEMICOLON = 1001;

    /**
     * Token type.
     *
     * @var int
     */
    private $type;

    /**
     * Token value.
     *
     * @var string
     */
    private $value;

    /**
     * Previous token in token list.
     *
     * @var Token|null
     */
    private $previous;

    /**
     * Next token in token list.
     *
     * @var Token|null
     */
    private $next;

    /**
     * Token constructor.
     *
     * @param int    $type  a Token type
     * @param string $value a Token value
     */
    public function __construct(int $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Creates a token from a raw PHP token value.
     *
     * @param string|array $token a raw PHP token
     *
     * @return self a Token object
     */
    public static function createFromValue($token): self
    {
        if (is_string($token)) {
            return self::createFromString($token);
        }

        if (!is_array($token)) {
            throw new \InvalidArgumentException(sprintf('Expected a string or an array, got a "%s".', strtolower(gettype($token))));
        }

        if (3 !== count($token) || array_keys($token) !== [0, 1, 2]) {
            throw new \InvalidArgumentException('Expected an array with 3 values, indexed 0, 1, 2, got something else.');
        }

        return new self($token[0], $token[1]);
    }

    /**
     * Creates a token from a raw PHP token string.
     *
     * @param string $token a raw PHP token string
     *
     * @return self a Token object
     */
    private static function createFromString(string $token): self
    {
        foreach (self::VALUE_OF_TYPES as $type => $value) {
            if ($value === $token) {
                return new Token($type, $token);
            }
        }

        throw new \InvalidArgumentException(sprintf('Unable to create token from string "%s".', $token));
    }

    /**
     * Returns the token type.
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Returns the token type name.
     */
    public function getTypeName(): string
    {
        return self::getTypeNameFromInteger($this->type);
    }

    /**
     * Returns the token type name.
     */
    public static function getTypeNameFromInteger(int $type): string
    {
        if ($type < 1000) {
            return token_name($type);
        }

        return self::NAME_OF_TYPES[$type] ?? 'Unknown';
    }

    /**
     * Returns the token value.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Changes the token value.
     *
     * @param string $value new token value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * Asserts the token has given type.
     *
     * @throws \InvalidArgumentException Token has a different type
     */
    public function ensureType(int $type): void
    {
        if ($this->type !== $type) {
            throw new \InvalidArgumentException(sprintf('Expected token type to be %s, got %s.', self::getTypeNameFromInteger($type), self::getTypeNameFromInteger($this->type)));
        }
    }

    /**
     * Returns the previous token, or null if it's the first in list.
     */
    public function getPrevious(): ?Token
    {
        return $this->previous;
    }

    /**
     * Changes the previous token, or remove it by providing null.
     */
    public function setPrevious(?Token $previous): void
    {
        $this->previous = $previous;
    }

    /**
     * Returns the next token, or null if it's the last in list.
     */
    public function getNext(?int $type = null): ?Token
    {
        $next = $this->next;
        if (null !== $type && $next) {
            $next->ensureType($type);
        } elseif (null !== $type) {
            throw new \InvalidArgumentException(sprintf('Expected token type to be %s, got end of file.', self::getTypeNameFromInteger($type)));
        }

        return $next;
    }

    /**
     * Changes the next token, or remove it by providing null.
     */
    public function setNext(?Token $next): void
    {
        $this->next = $next;
    }
}