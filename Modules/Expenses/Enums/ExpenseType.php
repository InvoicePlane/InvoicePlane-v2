<?php

namespace Modules\Expenses\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Traits\HasOptions;

enum ExpenseType: string implements LabeledEnum
{
    use HasOptions;

    case FIXED       = 'fixed';
    case ONE_TIME    = 'one_time';
    case RECURRING   = 'recurring';
    case TRAVEL      = 'travel';
    case UTILITY     = 'utility';
    case MAINTENANCE = 'maintenance';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::ONE_TIME    => 'One Time',
            self::RECURRING   => 'Recurring',
            self::TRAVEL      => 'Travel',
            self::UTILITY     => 'Utility',
            self::MAINTENANCE => 'Maintenance',
            self::FIXED       => 'Fixed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ONE_TIME    => 'gray',
            self::RECURRING   => 'blue',
            self::TRAVEL      => 'yellow',
            self::UTILITY     => 'purple',
            self::MAINTENANCE => 'red',
            self::FIXED       => 'sky',
        };
    }
}
