<?php

namespace PhpEditor;

class Classes implements \Countable, \IteratorAggregate
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
     * @return Class_[]
     */
    public function getAll(): array
    {
        return array_map(function (Token $token) {
            return new Class_($this->file, $token);
        }, $this->getClassTokens());
    }

    /**
     * @return Token[]
     */
    private function getClassTokens(): array
    {
        return $this->file->getTokens()->getAllByType([T_CLASS, T_INTERFACE]);
    }

    /**
     * @return Class_[]|iterable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getAll());
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->getAll());
    }

    public function get(string $className): Class_
    {
        foreach ($this as $class) {
            if ($class->getName() === $className) {
                return $class;
            }
        }

        throw new \InvalidArgumentException(sprintf('Found no class named "%s" in the given file.', $className));
    }

    public function has(string $className): bool
    {
        foreach ($this->getAll() as $class) {
            if ($class->getName() === $className) {
                return true;
            }
        }

        return false;
    }

    public function create(string $className, bool $isInterface = false): Class_
    {
        if ($this->has($className)) {
            throw new \LogicException(sprintf('The file already contains a class "%s".', $className));
        }

        $this->file->getOrCreateOpening();

        $tokens = $this->file->getTokens();

        $last = $tokens->getLast();
        $type = $isInterface ? 'interface' : 'class';
        $tokens->insertCodeAfter($last, "\n".$type.' '.$className."\n{\n}\n");

        return $this->get($className);
    }

    public function remove(string $className): Classes
    {
        $class = $this->get($className);
        $first = $class->getFirstToken();
        $last = $class->getLastToken();

        $previous = $first->getPrevious();
        $this->file->getTokens()->removeInterval($first, $last);
        $next = $last->getNext();

        if ($previous && $previous->isType(T_WHITESPACE) && "\n" === $previous->getValue()) {
            $this->file->getTokens()->removeInterval($previous, $previous);
        } elseif ($previous && $previous->isType(T_WHITESPACE) && "\n\n" === $previous->getValue()) {
            $previous->setValue("\n");
        }

        if ($next && $next->isType(T_WHITESPACE) && "\n" === $next->getValue()) {
            $this->file->getTokens()->removeInterval($next, $next);
        }

        return $this;
    }
}
