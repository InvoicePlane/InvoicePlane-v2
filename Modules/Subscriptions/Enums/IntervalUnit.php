<?php

namespace Modules\Subscriptions\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum IntervalUnit: string implements LabeledEnum
{
    case DAY   = 'day';
    case WEEK  = 'week';
    case MONTH = 'month';
    case YEAR  = 'year';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::DAY   => 'Day(s)',
            self::WEEK  => 'Week(s)',
            self::MONTH => 'Month(s)',
            self::YEAR  => 'Year(s)',
        };
    }

    public function color(): string
    {
        return 'gray';
    }
}
