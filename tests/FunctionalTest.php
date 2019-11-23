<?php

namespace PhpEditor\Tests;

use PhpEditor\File;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class FunctionalTest extends TestCase
{
    private $before;
    private $after;

    /**
     * @dataProvider provideCaseData
     */
    public function testCase(string $filePath)
    {
        list($this->before, $this->after) = $this->readBeforeAndAfterFromFile($filePath);

        if (null === $this->before) {
            $file = File::create();
        } else {
            $file = File::createFromSource($this->before);
        }

        require $filePath;

        if (null !== $this->after) {
            $this->assertEquals($this->after, $file->getSource());
        }
    }

    private function readBeforeAndAfterFromFile(string $filePath)
    {
        $content = file_get_contents($filePath);
        $before = $after = null;

        $reading = null;
        foreach (token_get_all($content) as $token) {
            if (T_START_HEREDOC === $token[0]) {
                if (!preg_match("/^<<<'(BEFORE|AFTER)'\n$/", $token[1])) {
                    throw new \RuntimeException(sprintf('Invalid heredoc syntax. Expected "<<<\'(BEFORE|AFTER)\'\n, got "%s".', $token[1]));
                }
                $reading = substr($token[1], 4, -2);

                continue;
            }

            if (T_END_HEREDOC === $token[0]) {
                $reading = null;

                continue;
            }

            if ('BEFORE' === $reading) {
                $before .= substr($token[1], 0, -1);
            } elseif ('AFTER' === $reading) {
                $after .= substr($token[1], 0, -1);
            }
        }

        return [$before, $after];
    }

    public function provideCaseData()
    {
        foreach (glob(__DIR__.'/cases/*.php') as $file) {
            yield [$file];
        }
    }
}
