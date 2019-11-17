Use statements
==============

Manipulate use statements of the file with ease, with the `Uses` object associated to a file.

First, [load your file](file.md) to have a `$file` object. Then, access the `Uses` object by running
`$file->getUses()`.

```php
$uses = $file->getUses();
```

Read use statements
-------------------

You can have a list of every use statements by calling `getAll` on `Uses` object:

```php
$all = $uses->getAll();
foreach ($all as list($class, $alias)) {
    echo $class;
    if ($alias !== null) {
        echo " as $alias";
    }
    echo "\n";
}
```

Add a use statement
-------------------

You can add or update a use statement with the `add` method on `Uses` object:

```php
// Add a simple use statement
$uses->add('Some\Class');
// File now contains:
// use Some\Class;

// Add a use statement with an alias
$uses->add('Some\Class', 'SomeClass');
// File now contains:
// use Some\Class as SomeClass;
```

Test presence of a use statement
--------------------------------

Test if a use statement is present in the code by calling `has` method on `Uses` object:

```php
if ($uses->has('Some\Class')) {
    echo "Some\Class is used";
}
```

Get alias of a use statement
----------------------------

To get the alias of a present use statement, use the method `getAlias` on `Uses` object:

```php
if ($uses->has('Some\Class')) {
    $alias = $uses->getAlias('Some\Class');
    echo "Some\Class is aliased as $alias";
```

**NB**: if the class  is not in use statements, an exception is thrown. Make sure to test with `has` before calling it.


Set alias of a use statement
----------------------------

Change the alias of a class by calling `setAlias`. Please notice that this is similar to `add` call, except that it will
throw an exception if the class was not found.

```php
$uses->setAlias('Some\Class', 'Alias');
// File now contains:
// use Some\Class as Alias;
```

You can also use this method to remove an alias:

```php
$uses->setAlias('Some\Class', null);
// File now contains:
// use Some\Class;
```

Remove use statement
--------------------

You can totally remove a use statement by calling `remove` method on `Uses` object:
```php
$uses->remove('Some\Class');
```
