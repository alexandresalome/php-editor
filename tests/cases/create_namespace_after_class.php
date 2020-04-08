<?php

/** @var PhpEditor\File $file */
/** @var PHPUnit\Framework\TestCase $this */
$file->getClasses()->create('Project');
$file->getNamespace()->set('Acme\Model');

<<<'AFTER'
<?php

namespace Acme\Model;

class Project
{
}

AFTER;
