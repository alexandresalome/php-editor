<?php

namespace PhpEditor;

class Uses
{
    /**
     * @var File
     */
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Returns a list of every use statements, consisting in a two dimensional array of string.
     *
     * For each row:
     *
     * - The first element is the class name
     * - The second element is the alias, or null if no alias is used
     *
     * ```
     * [
     *     ['\RuntimeException', null],
     *     ['Acme\Model\Project', 'ProjectModel'],
     *     ['Acme\Service\Project', 'ProjectService'],
     * ]
     * ```
     *
     * @return array
     */
    public function getAll()
    {
        return array_map([$this, 'readClassNameAndAlias'], $this->getUseTokens());
    }

    /**
     * Tests the presence of a class in use statements.
     */
    public function has(string $class): bool
    {
        foreach ($this->getUseTokens() as $token) {
            list($actual) = $this->readClassNameAndAlias($token);
            if ($actual === $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a class to use statements.
     */
    public function add(string $class, ?string $alias = null): Uses
    {
        // Already exists
        if ($this->has($class)) {
            $this->setAlias($class, $alias);

            return $this;
        }

        $line = 'use '.(null === $alias ? $class : $class.' as '.$alias).";\n";

        // Insert by alphabetical order
        foreach ($this->getUseTokens() as $token) {
            list($className) = $this->readClassNameAndAlias($token);
            if ($className > $class) {
                $this->file->getTokens()->insertCodeBefore($token, $line);

                return $this;
            }
        }

        // No use statement, insert it
        $namespace = $this->file->getTokens()->getAllByType(T_NAMESPACE);
        $after = '';
        if (count($namespace)) {
            $token = $namespace[0]->getNext(T_WHITESPACE)->getNext();
            while ($token->isType([T_STRING, T_NS_SEPARATOR])) {
                $token = $token->getNext(Token::TYPE_ENDING_SEMICOLON)->getNext();
                if ("\n\n" === $token->getValue()) {
                    $token->setValue("\n");
                    $after = "\n";
                }
            }
        } else {
            $token = $this->file->getOrCreateOpening();
        }

        $this->file->getTokens()->insertCodeAfter($token, "\n".$line.$after);

        return $this;
    }

    /**
     * Add, changes or remove the alias of a class in use statements.
     */
    public function setAlias(string $class, ?string $alias): Uses
    {
        foreach ($this->getUseTokens() as $token) {
            list($actualClass) = $this->readClassNameAndAlias($token);
            if ($actualClass === $class) {
                $this->setAliasFromToken($token, $alias);

                return $this;
            }
        }

        throw new \InvalidArgumentException(sprintf('File does not contain use for class "%s".', $class));
    }

    /**
     * Returns the alias defined for a given class.
     *
     * If the class is not aliased, it will return null.
     */
    public function getAlias(string $class): ?string
    {
        foreach ($this->getUseTokens() as $token) {
            list($actual, $alias) = $this->readClassNameAndAlias($token);
            if ($actual === $class) {
                return $alias;
            }
        }

        throw new \InvalidArgumentException(sprintf('Class "%s" is not in use statements.', $class));
    }

    /**
     * Remove use statement for a given class.
     */
    public function remove(string $class): Uses
    {
        foreach ($this->getUseTokens() as $token) {
            list($actual) = $this->readClassNameAndAlias($token);
            if ($actual === $class) {
                $this->removeFromToken($token);
            }
        }

        return $this;
    }

    private function getUseTokens()
    {
        return $this->file->getTokens()->getAllByType(T_USE);
    }

    private function readClassNameAndAlias(Token $token): array
    {
        // move cursor to beginning of the class
        $token = $token->ensureType(T_USE)->getNext(T_WHITESPACE)->getNext();

        // Read classname
        $className = '';
        while ($token->isType([T_STRING, T_NS_SEPARATOR])) {
            $className .= $token->getValue();
            $token = $token->getNext();
        }

        if ($token->isType(T_WHITESPACE)) {
            $token = $token->getNext();
        }

        $alias = null;
        if ($token->isType(T_AS)) {
            /** @var Token $token */
            $token = $token->getNext(T_WHITESPACE);
            $token = $token->getNext(T_STRING);
            $alias = $token->getValue();
        }

        return [$className, $alias];
    }

    private function removeFromToken(Token $token): void
    {
        $token->ensureType(T_USE);

        $begin = $token;
        while (!$token->isType(Token::TYPE_ENDING_SEMICOLON)) {
            $token = $token->getNext();
        }

        if ($token->isNext(T_WHITESPACE) && "\n" === $token->getNext()->getValue()) {
            $token = $token->getNext();
        }

        if ($token->isNext(T_WHITESPACE) && "\n\n" === $token->getNext()->getValue()) {
            $token->getNext()->setValue("\n");
        }

        $before = $begin->getPrevious();
        $after = $token->getNext();
        $this->file->getTokens()->removeInterval($begin, $token);

        if ($before->isType(T_WHITESPACE) && $after->isType(T_WHITESPACE) && 0 === count($this->file->getTokens()->getAllByType(T_USE))) {
            $tokens = $this->file->getTokens();
            $offset = $tokens->getTokenOffset($after);
            unset($tokens[$offset]);
        }
    }

    private function setAliasFromToken(Token $token, ?string $alias): void
    {
        $token = $token->ensureType(T_USE)->getNext(T_WHITESPACE)->getNext();

        // Read classname
        $className = '';
        while ($token->isType([T_STRING, T_NS_SEPARATOR])) {
            $className .= $token->getValue();
            $token = $token->getNext();
        }

        if ($token->isType(T_WHITESPACE)) {
            $token = $token->getNext();
        }

        $token->ensureType([T_AS, Token::TYPE_ENDING_SEMICOLON]);

        // Change "as ..."
        if ($token->isType(T_AS) && null !== $alias) {
            /** @var Token $token */
            $token = $token
                ->getNext(T_WHITESPACE)
                ->getNext(T_STRING)
            ;
            $token->setValue($alias);

            return;
        }

        // Remove "as ..."
        if ($token->isType(T_AS) && null === $alias) {
            $end = $token->getNext(T_WHITESPACE)->getNext(T_STRING);
            $this->file->getTokens()->removeInterval($token->getPrevious(), $end);

            return;
        }

        // No "as ..." alias
        if ($token->isType(Token::TYPE_ENDING_SEMICOLON) && null !== $alias) {
            $this->file->getTokens()->insertCodeBefore($token, ' as '.$alias);
        }
    }
}
