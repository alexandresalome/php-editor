<?php

<<<'BEFORE'
<?php
/** Some comment */
class Foo extends Bar {}
BEFORE;

/** @var PhpEditor\File $file */
$foo = $file->getClasses()->get('Foo');
$foo->removeExtends();

<<<'AFTER'
<?php
/** Some comment */
class Foo {}
AFTER;
