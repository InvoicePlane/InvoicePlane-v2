<?php

namespace Modules\Invoices\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum RecurringFrequency: string implements LabeledEnum
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
            self::DAILY     => trans('enums.recurring_frequency.daily'),
            self::WEEKLY    => trans('enums.recurring_frequency.weekly'),
            self::MONTHLY   => trans('enums.recurring_frequency.monthly'),
            self::YEARLY    => trans('enums.recurring_frequency.yearly'),
            self::QUARTERLY => trans('enums.recurring_frequency.quarterly'),
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
