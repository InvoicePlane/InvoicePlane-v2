<?php

namespace Modules\Subscriptions\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum CancellationType: string implements LabeledEnum
{
    case IMMEDIATE     = 'immediate';
    case AT_PERIOD_END = 'at_period_end';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::IMMEDIATE     => trans('ip.cancellation_type_immediate'),
            self::AT_PERIOD_END => trans('ip.cancellation_type_at_period_end'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IMMEDIATE     => 'danger',
            self::AT_PERIOD_END => 'warning',
        };
    }
}
