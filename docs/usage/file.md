File
====

The entry point of this library is surely the file. Even if you don't have a file on disk, you can use this object to
modelize the source you want to edit, even maybe start with an empty file.

Create an empty file
--------------------

You can create an empty file by calling static method `create` on `File`:

```php
use PhpEditor\File;

$file = File::create();
```

Any edit method will automatically add the required opening tag.

Open an existing file
---------------------

Call the static `createFromFile` method from the `File` class:

```php
use PhpEditor\File;

$file = File::createFromFile('/path/to/file.php');
```

Create from PHP source code
---------------------------

You can also create the `File` object from raw PHP code in a string by calling the method `createFromSource` on class
`File`:

```php
use PhpEditor\File;

$file = File::createFromSource('<?php echo "Hello world!";');
```

Save modifications to file
--------------------------

After modifications, write the changes by calling `saveToFile` on `File` object:

```php
$file = File::createFromPath('/path/to/file.php');

$file->getNamespace()->setName('Foo');

$file->saveToFile('/path/to/file_modified.php');
```

Get modified source code
------------------------

You can fetch the modified source code by calling `getSource` on a `File` object:

```php
$file = File::createFromPath('/path/to/file.php');

$file->getNamespace()->setName('Foo');

echo $file->getSource(); // displays modified code
```
