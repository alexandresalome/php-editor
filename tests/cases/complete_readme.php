<?php

/** @var PhpEditor\File $file */

// Change the namespace
$file->getNamespace()->set('Acme\Model');

// Add a use statement
$file->getUses()->add('Acme\Shared\Model\BaseModel');

// Get the class definition
$classDefinition = $file->getClasses()->create('Project');

// Change the extended class
$classDefinition->setExtends('BaseModel');

<<<'AFTER'
<?php

namespace Acme\Model;

use Acme\Shared\Model\BaseModel;

class Project extends BaseModel
{
}

AFTER;
