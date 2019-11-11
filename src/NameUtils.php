<?php

namespace PhpEditor;

/**
 * Utilities for class name manipulation.
 */
class NameUtils
{
    public static function isNamespaced(string $className): bool
    {
        return false !== strpos($className, '\\');
    }
}
