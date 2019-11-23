<?php

<<<'BEFORE'
<?php
/** Some comment */
class Foo {}
BEFORE;

/** @var PhpEditor\File $file */
$foo = $file->getClasses()->get('Foo');
$foo->setExtends('Bar');

<<<'AFTER'
<?php
/** Some comment */
class Foo extends Bar {}
AFTER;
