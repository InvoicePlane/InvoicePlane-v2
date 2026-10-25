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
            self::ONE_TIME    => trans('enums.expense_type.one_time'),
            self::RECURRING   => trans('enums.expense_type.recurring'),
            self::TRAVEL      => trans('enums.expense_type.travel'),
            self::UTILITY     => trans('enums.expense_type.utility'),
            self::MAINTENANCE => trans('enums.expense_type.maintenance'),
            self::FIXED       => trans('enums.expense_type.fixed'),
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
