<?php

namespace PhpEditor;

/**
 * Represents a class or an interface.
 */
class Class_
{
    /**
     * The file containing the class definition.
     *
     * @var File
     */
    private $file;

    /**
     * Token referring to "class" word in a class definition.
     *
     * @var Token
     */
    private $token;

    /**
     * Constructs a clas object.
     *
     * @param File  $file  A file object
     * @param Token $token A token object
     */
    public function __construct(File $file, Token $token)
    {
        $this->file = $file;
        $this->token = $token;
    }

    /**
     * Returns the name of the class.
     *
     * @return string Class name
     */
    public function getName(): string
    {
        return $this->getNameToken()->getValue();
    }

    /**
     * Changes the class name.
     *
     * @param string $name A class name
     *
     * @return Class_ fluid interface
     */
    public function setName(string $name): Class_
    {
        $this->getNameToken()->setValue($name);

        return $this;
    }

    /**
     * Tests if the instance is a class.
     *
     * @return bool Test result
     */
    public function isClass(): bool
    {
        return $this->token->isType(T_CLASS);
    }

    /**
     * Tests if the instance is an interface.
     *
     * @return bool Test result
     */
    public function isInterface(): bool
    {
        return $this->token->isType(T_INTERFACE);
    }

    /**
     * Tests if the class is abstract.
     *
     * @return bool Test result
     */
    public function isAbstract(): bool
    {
        $previous = $this->token->getPreviousNotEmpty();

        return null !== $previous && $previous->isType(T_ABSTRACT);
    }

    public function isFinal(): bool
    {
        $previous = $this->token->getPreviousNotEmpty();

        return null !== $previous && $previous->isType(T_FINAL);
    }

    public function setFinal(bool $isFinal = true): Class_
    {
        if ($this->isFinal() === $isFinal) {
            return $this;
        } elseif ($isFinal) {
            $this->file->getTokens()->insertCodeBefore($this->token, 'final ');

            return $this;
        } else {
            $end = $this->token->getPrevious(T_WHITESPACE);
            $begin = $end->getPrevious(T_FINAL);

            $this->file->getTokens()->removeInterval($begin, $end);
        }

        return $this;
    }

    public function setAbstract(bool $isAbstract = true): Class_
    {
        if ($this->isAbstract() === $isAbstract) {
            return $this;
        } elseif ($isAbstract) {
            $this->file->getTokens()->insertCodeBefore($this->token, 'abstract ');

            return $this;
        } else {
            $end = $this->token->getPrevious(T_WHITESPACE);
            $begin = $end->getPrevious(T_ABSTRACT);

            $this->file->getTokens()->removeInterval($begin, $end);
        }

        return $this;
    }

    /**
     * Gets the value of the extends statement, or null if not found.
     *
     * @return string|null The extends value, or null if not found
     */
    public function getExtends(): ?string
    {
        $nameToken = $this->getNameToken();
        $next = $nameToken->getNextNotEmpty();
        if ($next->isType(T_EXTENDS)) {
            return $next->getNextNotEmpty(T_STRING)->getValue();
        }

        return null;
    }

    public function setExtends(string $name): Class_
    {
        $nameToken = $this->getNameToken();
        $next = $nameToken->getNextNotEmpty();
        if (!$next->isType(T_EXTENDS)) {
            $this->file->getTokens()->insertCodeAfter($nameToken, ' extends '.$name);

            return $this;
        }

        $nameToken = $next->getNextNotEmpty(T_STRING);
        $nameToken->setValue($name);

        return $this;
    }

    public function removeExtends(): Class_
    {
        $nameToken = $this->getNameToken();
        $begin = $nameToken->getNext();
        $next = $nameToken->getNextNotEmpty();
        if (!$next->isType(T_EXTENDS)) {
            return $this;
        }
        $end = $next->getNextNotEmpty(T_STRING);

        $this->file->getTokens()->removeInterval($begin, $end);

        return $this;
    }

    public function getImplements(): array
    {
        $nameToken = $this->getNameToken();
        $next = $nameToken->getNextNotEmpty();

        if ($next->isType(T_EXTENDS)) {
            $next = $next->getNextNotEmpty(T_STRING)->getNextNotEmpty();
        }

        if (!$next->isType(T_IMPLEMENTS)) {
            return [];
        }

        $next = $next->getNextNotEmpty();

        $result = [];
        while ($next->isType(T_STRING)) {
            $result[] = $next->getValue();
            $next = $next->getNextNotEmpty();
            /** @var Token $next */
            if (null !== $next && !$next->isType(Token::TYPE_COMMA)) {
                break;
            }

            $next = $next->getNextNotEmpty();
        }

        return $result;
    }

    public function setImplements(array $implements): Class_
    {
        $existing = $this->getImplements();
        foreach ($implements as $class) {
            if (!in_array($class, $existing)) {
                $this->addToImplements($class);
            }
        }

        foreach ($existing as $class) {
            if (!in_array($class, $implements)) {
                $this->removeFromImplements($class);
            }
        }

        return $this;
    }

    public function hasInImplements(string $name): bool
    {
        return in_array($name, $this->getImplements());
    }

    public function addToImplements(string $class): Class_
    {
        if ($this->hasInImplements($class)) {
            return $this;
        }

        $token = $this->getNameToken();
        $next = $token->getNextNotEmpty();

        // jump over extends
        if ($next->isType(T_EXTENDS)) {
            $token = $next->getNextNotEmpty(T_STRING);
            $next = $token->getNextNotEmpty();
        }

        // no implements, add new one
        if (!$next->isType(T_IMPLEMENTS)) {
            $this->file->getTokens()->insertCodeAfter($token, ' implements '.$class);

            return $this;
        }

        // Move to implemented class
        $token = $next->getNextNotEmpty(T_STRING);

        // already has implements, insert alphabetically
        while ($token->isType(T_STRING)) {
            $lastName = $token;
            if ($token->getValue() > $class) {
                $this->file->getTokens()->insertCodeBefore($token, $class.', ');

                return $this;
            }

            /** @var Token $token */
            $token = $token->getNextNotEmpty();
            if (null !== $token && $token->isType(Token::TYPE_COMMA)) {
                $token = $token->getNextNotEmpty();
            }
        }

        // last element, insert in the end
        $this->file->getTokens()->insertCodeAfter($lastName, ', '.$class);

        return $this;
    }

    public function removeFromImplements(string $class): Class_
    {
        if (!$this->hasInImplements($class)) {
            return $this;
        }

        $token = $this->getNameToken();
        $next = $token->getNextNotEmpty();

        // jump over extends
        if ($next->isType(T_EXTENDS)) {
            $token = $next->getNextNotEmpty(T_STRING);
            $next = $token->getNextNotEmpty();
        }

        $next->ensureType(T_IMPLEMENTS);

        // Move to implemented class
        $token = $next->getNextNotEmpty(T_STRING);

        // search for token to remove
        while ($token->isType(T_STRING)) {
            if ($token->getValue() === $class) {
                break;
            }

            /** @var Token $token */
            $token = $token->getNextNotEmpty();
            if (null !== $token && $token->isType(Token::TYPE_COMMA)) {
                $token = $token->getNextNotEmpty();

                continue;
            }
        }

        $token->ensureType(T_STRING);
        $previous = $token->getPreviousNotEmpty();
        $next = $token->getNextNotEmpty();

        if ($previous->isType(T_IMPLEMENTS) && $next->isType(Token::TYPE_BRACKET_OPENING)) {
            $previous = $previous->getPrevious(T_WHITESPACE);
            $this->file->getTokens()->removeInterval($previous, $token);
        } elseif ($previous->isType(Token::TYPE_COMMA)) {
            $this->file->getTokens()->removeInterval($previous, $token);
        } else {
            $next = $next->getNext(T_WHITESPACE);
            $this->file->getTokens()->removeInterval($token, $next);
        }

        return $this;
    }

    public function getNameToken(): Token
    {
        return $this->token
            ->getNextNotEmpty(T_STRING)
        ;
    }

    public function getFirstToken(): Token
    {
        $token = $this->token;
        $previous = $this->token->getPreviousNotEmpty();
        if ($previous && $previous->isType([T_ABSTRACT, T_FINAL])) {
            $token = $previous;
        }

        return $token;
    }

    public function getLastToken(): Token
    {
        $token = $this->token;
        while ($token && !$token->isType(Token::TYPE_BRACKET_OPENING)) {
            $token = $token->getNext();
        }

        if (!$token) {
            throw new \RuntimeException(sprintf('Found no opening bracket for class "%s".', $this->getName()));
        }

        $counter = 1;
        while ($counter > 0) {
            $token = $token->getNext();
            if (!$token) {
                break;
            }

            if ($token->isType(Token::TYPE_BRACKET_OPENING)) {
                ++$counter;
            } elseif ($token->isType(Token::TYPE_BRACKET_CLOSING)) {
                --$counter;
            }
        }

        if ($counter > 0) {
            throw new \RuntimeException(sprintf('Number of opening/closing brackets for class "%s" do not match.', $this->getName()));
        }

        return $token;
    }
}
