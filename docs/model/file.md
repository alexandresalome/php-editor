File
====

The ``File`` class is the main entry point of the library.

**Methods**:

- [Constructor](#constructor)
- [File::create](#file-create)
- [File::createFromFile](#file-createfromfile)
- [File::createFromSource](#file-createfromsource)
- [saveToFile](#savetofile)

File::create
------------

**Signature**

> ``File::create(): File``

**Description**

Creates an empty file.

File::createFromSource
----------------------

**Signature**

> ``File::createFromSource(string $source): File``

**Description**

Creates from a PHP sourcecode string.

File::createFromFile
--------------------

**Signature**

> ``File::createFromFile(string $filePath): File``

**Description**

Creates from an existing file.

saveToFile
----------

**Signature**

> ``$file->saveToFile(string $file)``

**Description**

Writes the model to a file.
