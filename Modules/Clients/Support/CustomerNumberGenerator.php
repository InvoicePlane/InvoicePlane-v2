<?php

namespace Modules\Clients\Support;

use Modules\Core\Support\NumberGenerator\AbstractNumberGenerator;

class CustomerNumberGenerator extends AbstractNumberGenerator
{
    protected string $type = 'Customer';

    protected ?string $groupName = 'Default Customer Numbering';
}
