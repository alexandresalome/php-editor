Namespace
=========

You can edit the namespace of PHP code by using the **Namespace** object associated to a file.

First, [load your file](file.md) to have a `$file` object. Then, access the `Namespace_` object by running
`$file->getNamespace()`.

**NB**: the class is named `Namespace_` because **namespace** is a reserved word in PHP.

Test if the file has a namespace
--------------------------------

To detect if there is a **namespace** statement in the file, you can call the `exists()` method on the `Namespace_`
object:

```php
<?php

if ($file->getNamespace()->exists()) {
    echo "File has a namespace\n";
} else {
    echo "File has no namespace defined\n";
}
```

Read the file namespace
-----------------------

Access the current namespace by calling the `get()` method on the `Namespace_` object:

```php
<?php

$namespace = $file->getNamespace()->get();

if ($namespace === null) {
    echo "No namespace found\n";
} else {
    echo "Namespace: $namespace\n";
}
```

`getNamespace()->get()` can return **null**, if no namespace is defined in the file. Otherwise, this method returns
a string containing the file namespace.

Change the file namespace
-------------------------

To modify the file namespace, use the `set()` method on the `Namespace_` object:

```php
<?php

$file->getNamespace()->set('Acme\Project');
```
