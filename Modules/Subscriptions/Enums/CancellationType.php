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
            self::IMMEDIATE     => 'Cancel Immediately',
            self::AT_PERIOD_END => 'Cancel at End of Billing Period',
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
