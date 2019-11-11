File
====

The ``File`` class is the main entry point of the library.

**Methods**:

- [Constructor](#constructor)
- [File::createFromFile](#file-create-from-file)

Constructor
-----------

**Signature**

> ``__construct(?Tokens $tokens = null)``

**Description**

You can instantiate this object without argument:

```php
use PHPEditor\File;

$file = new File();
```

This will create an empty file.

File::createFromFile
--------------------

**Signature**

> ``File::createFromFile(string $filePath): File``

File::createFromSource
----------------------

**Signature**

> ``File::createFromSource(string $source): File``
