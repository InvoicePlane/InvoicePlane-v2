<?php

namespace Modules\Expenses\Enums;

use Modules\Expenses\Enums\ExpenseType;

use Modules\Core\Support\Results\Expenses;

use Modules\Core\Contracts\LabeledEnum;


enum ExpenseType: string implements LabeledEnum
{
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
        };
    }
}
