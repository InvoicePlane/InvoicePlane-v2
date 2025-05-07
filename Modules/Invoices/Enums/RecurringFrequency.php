<?php

namespace Modules\Invoices\Enums;

use Modules\Invoices\Enums\RecurringFrequency;

use Modules\Core\Contracts\LabeledEnum;

use Modules\Core\Support\Results\Invoices;

enum RecurringFrequency: string implements \Modules\Core\Contracts\LabeledEnum
{
    case DAILY     = 'daily';
    case WEEKLY    = 'weekly';
    case MONTHLY   = 'monthly';
    case QUARTERLY = 'quarterly';
    case YEARLY    = 'yearly';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::DAILY     => 'Daily',
            self::WEEKLY    => 'Weekly',
            self::MONTHLY   => 'Monthly',
            self::YEARLY    => 'Yearly',
            self::QUARTERLY => 'Quarterly',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DAILY     => 'gray',
            self::WEEKLY    => 'info',
            self::MONTHLY   => 'success',
            self::YEARLY    => 'warning',
            self::QUARTERLY => 'yellow',
        };
    }
}
