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
            self::DAY   => trans('ip.interval_unit_day'),
            self::WEEK  => trans('ip.interval_unit_week'),
            self::MONTH => trans('ip.interval_unit_month'),
            self::YEAR  => trans('ip.interval_unit_year'),
        };
    }

    public function color(): string
    {
        return 'gray';
    }
}
