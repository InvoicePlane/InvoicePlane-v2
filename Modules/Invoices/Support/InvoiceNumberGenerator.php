<?php

namespace Modules\Invoices\Support;

use Modules\Core\Support\NumberGenerator\AbstractNumberGenerator;

class InvoiceNumberGenerator extends AbstractNumberGenerator
{
    protected string $type = 'Invoice';

    protected ?string $groupName = 'Default Invoice Numbering';
}
