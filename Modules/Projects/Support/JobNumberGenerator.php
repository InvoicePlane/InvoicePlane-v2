<?php

namespace Modules\Projects\Support;

use Modules\Core\Support\NumberGenerator\AbstractNumberGenerator;

class JobNumberGenerator extends AbstractNumberGenerator
{
    protected string $type = 'Job';

    protected ?string $groupName = 'Default Job Numbering';
}
