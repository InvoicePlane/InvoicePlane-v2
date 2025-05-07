<?php

namespace Modules\Expenses\Enums;

use Modules\Core\Support\Results\Expenses;

use Modules\Expenses\Enums\ExpenseStatus;

use Modules\Core\Contracts\LabeledEnum;


enum ExpenseStatus: string implements LabeledEnum
{
    case PENDING   = 'pending';
    case COMPLETED = 'completed';
    case FAILED    = 'failed';
    case REFUNDED  = 'refunded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending',
            self::COMPLETED => 'Completed',
            self::FAILED    => 'Failed',
            self::REFUNDED  => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING   => 'gray',
            self::COMPLETED => 'green',
            self::FAILED    => 'maroon',
            self::REFUNDED  => 'emerald',
        };
    }
}
