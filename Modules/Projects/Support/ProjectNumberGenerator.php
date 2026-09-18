<?php

namespace Modules\Projects\Support;

use Modules\Core\Support\NumberGenerator\AbstractNumberGenerator;

class ProjectNumberGenerator extends AbstractNumberGenerator
{
    protected string $type = 'Project';

    protected ?string $groupName = 'Default Project Numbering';
}
