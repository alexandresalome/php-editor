<?php

/** @var PhpEditor\File $file */
/** @var PHPUnit\Framework\TestCase $this */
$file->getNamespace()->set('Acme\Model');
$file->getClasses()->create('Project');

<<<'AFTER'
<?php

namespace Acme\Model;

class Project
{
}

AFTER;
