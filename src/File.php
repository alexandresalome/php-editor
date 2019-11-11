<?php

namespace PhpEditor;

/**
 * The ``File`` class is the main entry point of the library.
 */
class File
{
    /**
     * @var Tokens
     */
    private $tokens;

    /**
     * @var Namespace_
     */
    private $namespace;

    /**
     * @param Tokens|null $tokens a list of tokens
     */
    private function __construct(?Tokens $tokens = null)
    {
        $this->tokens = $tokens ?: Tokens::createFromSource('');

        $this->ensureMonolithic();
        $this->namespace = new Namespace_($this);
    }

    public static function create(): File
    {
        return new File();
    }

    public static function createFromSource(string $source): File
    {
        return new self(Tokens::createFromSource($source));
    }

    public static function createFromFile(string $file): File
    {
        return new self(Tokens::createFromFile($file));
    }

    public function saveToFile(string $file)
    {
        file_put_contents($file, $this->getSource());
    }

    public function hasNamespace(): bool
    {
        return $this->namespace->exists();
    }

    public function getNamespace(): ?string
    {
        return $this->namespace->getName();
    }

    public function setNamespace(string $namespaceName): File
    {
        $this->namespace->setName($namespaceName);

        return $this;
    }

    public function getSource(): string
    {
        return $this->tokens->getSource();
    }

    public function getOrCreateOpening(): Token
    {
        if (!isset($this->tokens[0])) {
            $this->tokens->push(new Token(T_OPEN_TAG, "<?php\n"));
        }

        return $this->tokens[0];
    }

    public function getTokens(): Tokens
    {
        return $this->tokens;
    }

    private function ensureMonolithic(): void
    {
        if (!isset($this->tokens[0])) {
            return;
        }

        $token = $this->tokens[0];

        if (T_OPEN_TAG !== $token->getType() || count($this->tokens->getAllByType(T_CLOSE_TAG))) {
            throw new \RuntimeException('Only monolithic files are supported (starting with <?php, no close tag).');
        }
    }
}
