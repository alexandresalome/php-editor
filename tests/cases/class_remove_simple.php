<?php

<<<'BEFORE'
<?php

namespace Acme\Model;

use Acme\Shared\Model\BaseModel;

class Project extends BaseModel {}

BEFORE;

/** @var PhpEditor\File $file */
$foo = $file->getClasses()->remove('Project');

<<<'AFTER'
<?php

namespace Acme\Model;

use Acme\Shared\Model\BaseModel;

AFTER;
