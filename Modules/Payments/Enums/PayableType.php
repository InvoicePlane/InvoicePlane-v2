<?php

namespace Modules\Payments\Enums;

use Modules\Core\Support\Results\Payments;

use Modules\Invoices\Models\Invoice;

use Modules\Core\Contracts\LabeledEnum;

use Modules\Payments\Enums\PayableType;

enum PayableType: string implements \Modules\Core\Contracts\LabeledEnum
{
    case INVOICE = 'invoice';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::INVOICE => 'Invoice',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INVOICE => 'info',
        };
    }
}
