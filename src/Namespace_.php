<?php

namespace PhpEditor;

class Namespace_
{
    /**
     * @var File
     */
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
        $this->getNamespaceToken(); // ensure namespace count
    }

    public function exists(): bool
    {
        return null !== $this->getNamespaceToken();
    }

    public function get(): ?string
    {
        $token = $this->getNamespaceToken();

        if (null === $token) {
            return null;
        }

        return $token
            ->getNextNotEmpty(T_STRING)
            ->getValue()
        ;
    }

    public function set(string $name): void
    {
        $token = $this->getNamespaceToken();

        if (null === $token) {
            $opening = $this->file->getOrCreateOpening();
            $this->file->getTokens()->insertCodeAfter($opening, "\nnamespace $name;\n");

            return;
        }

        $token
            ->getNextNotEmpty(T_STRING)
            ->setValue($name)
        ;
    }

    private function getNamespaceToken(): ?Token
    {
        $namespaces = $this->file->getTokens()->getAllByType(T_NAMESPACE);
        if (count($namespaces) > 1) {
            throw new \RuntimeException('Only files with zero or one namespace are supported.');
        }

        return $namespaces[0] ?? null;
    }
}
