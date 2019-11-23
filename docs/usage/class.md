Class
=====

You can edit classes contained in a file by using the `Classes` object
associated to a file:

```php
$classes = $file->getClasses();
```

The `Classes` object is the collection file for classes defined in the file. If
you only have one class defined in a file, you can use:

```php
$class = $file->getClass();
```

This method returns a `Class_` object.

**Notice**: this method only works if you have a single class defined in the
file. If you have no class or more than one class defined, an error will be
thrown. In this case, use the `Classes` object method to access or create the
desired class.

Get list of classes defined in a file
-------------------------------------

The `Classes` object has a `getAll()` method returning a list of `Class_` object:

```php
foreach ($classes->getAll() as $class) {
    echo "Class ".$class->getName()."\n";
}
```

The `Classes` object is also iterable and countable, so you can directly do:

```php
echo "Total count of classes: ".count($classes)."\n";
foreach ($classes as $class) {
    echo "- ".$class->getName()."\n";
}
```

Test if a class exists
----------------------

Verify the presence of a class by using `has($name)` method:

```
if ($classes->has('Project')) {
    echo "Project class found\n";
}
```

Remove a class
--------------

Call the `remove(string $name)` method:

```
$classes->remove('Project');
```

Create a class
--------------

Call the `create(string $name)` method:

```php
$class = $classes->create('Project');
$class->setExtends('BaseModel');
```

Abstract classes
----------------

Two methods exist to manipulate abstract prefix of a class:

- `isAbstract()`
- `setAbstract(bool $abstract = true)`

```
// Switch the abstract
$class->setAbstract(!$class->isAbstract());
```

Extends classes
---------------
