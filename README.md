PHP Editor
==========

[![Automated tests status](https://github.com/alexandresalome/php-editor/workflows/Automated%20tests/badge.svg)](https://github.com/alexandresalome/php-editor/actions?query=workflow%3A%22Automated+tests%22)

A library to edit PHP code.

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

// Edit the file
$file->setNamespace('Foo');

// Save the file
$file->saveToFile('/path/to/file_modified.php');
```

Documentation
-------------

- [Documentation of the model](doc/model.md)
- [Changelog](CHANGELOG.md)

Limitations
-----------

This library has some limitations for now:

- Only works for monolithic PHP files (starts with `<?php`, no ending tag)
- Only works for PHP files with zero or one namespace defined
