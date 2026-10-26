<?php

namespace Modules\Quotes\Support;

use Modules\Core\Support\NumberGenerator\AbstractNumberGenerator;

class QuoteNumberGenerator extends AbstractNumberGenerator
{
    protected string $type = 'Quote';

    protected ?string $groupName = 'Default Quote Numbering';
}
