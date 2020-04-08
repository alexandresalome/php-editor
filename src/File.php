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
     * @var Uses
     */
    private $uses;

    /**
     * @var Classes
     */
    private $classes;

    /**
     * @param Tokens|null $tokens a list of tokens
     */
    private function __construct(?Tokens $tokens = null)
    {
        $this->tokens = $tokens ?: Tokens::createFromSource('');

        $this->ensureMonolithic();
        $this->namespace = new Namespace_($this);
        $this->classes = new Classes($this);
        $this->uses = new Uses($this);
    }

    /**
     * Creates an empty file.
     */
    public static function create(): File
    {
        return new File();
    }

    /**
     * Creates from a PHP sourcecode string.
     */
    public static function createFromSource(string $source): File
    {
        return new self(Tokens::createFromSource($source));
    }

    /**
     * Creates from an existing file.
     */
    public static function createFromFile(string $file): File
    {
        return new self(Tokens::createFromFile($file));
    }

    /**
     * Writes the model to a file.
     */
    public function saveToFile(string $file)
    {
        file_put_contents($file, $this->getSource());
    }

    public function getNamespace(): Namespace_
    {
        return $this->namespace;
    }

    public function getUses(): Uses
    {
        return $this->uses;
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

    /**
     * @internal
     */
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

    public function getClasses(): Classes
    {
        return $this->classes;
    }

    public function getClass(): Class_
    {
        $classes = iterator_to_array($this->classes);

        if (1 !== count($classes)) {
            throw new \LogicException(sprintf('Expected exactly one definition, got %d.', count($classes)));
        }

        return $classes[0];
    }
}
