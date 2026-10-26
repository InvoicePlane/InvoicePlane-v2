<?php

namespace Modules\Projects\Support;

use Modules\Core\Support\NumberGenerator\AbstractNumberGenerator;

class TaskNumberGenerator extends AbstractNumberGenerator
{
    protected string $type = 'Task';

    protected ?string $groupName = 'Default Task Numbering';
}
