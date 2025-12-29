<?php

namespace Modules\Projects\Support;

use Modules\Core\Support\NumberGenerator\AbstractNumberGenerator;

class JobCardNumberGenerator extends AbstractNumberGenerator
{
    protected string $type = 'JobCard';

    protected ?string $groupName = 'Default JobCard Numbering';
}
