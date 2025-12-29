<?php

namespace Modules\Payments\Support;

use Modules\Core\Support\NumberGenerator\AbstractNumberGenerator;

class PaymentNumberGenerator extends AbstractNumberGenerator
{
    protected string $type = 'Payment';

    protected ?string $groupName = 'Default Payment Numbering';
}
