PHP Editor
==========

[![Automated tests status](https://github.com/alexandresalome/php-editor/workflows/Automated%20tests/badge.svg)](https://github.com/alexandresalome/php-editor/actions?query=workflow%3A%22Automated+tests%22)

A library to edit PHP code.

**Warning**! this project is not stable and API might change until the version 1.0.0 is released (no planned date for
now).

Installation
------------

Install this library with **[composer](https://getcomposer.org)**:

```
composer install alexandresalome/php-editor
```

Usage
-----

```php
<?php

use PhpEditor\File;

// Open the file
$file = File::createFromFile('/path/to/file.php');

// Change the namespace
$file->getNamespace()->set('Acme\Model');

// Add a use statement
$file->getUses()->add('Acme\Shared\Model\BaseModel');

// Get the class definition
$classDefinition = $file->getClass();

// Change the extended class
$classDefinition->setExtends('BaseModel');

// Save the file
$file->saveToFile('/path/to/file_modified.php');
```

Documentation
-------------

- [Usage manual](doc/usage.md)
- [Model reference](doc/model.md)
- [Changelog](CHANGELOG.md)

Limitations
-----------

This library has some limitations for now:

- Only monolithic PHP files (starts with `<?php`, no ending tag) - #11
- Only PHP files with zero or one namespace defined - #12
- Does not support multi-use syntax (`use Namespace\{Foo, Bar, Baz}`) - #13
- Does not support `class X extends Some\Separated\Class` - #14

Please refer to those tickets if you want to upvote or contribute on those topics.
