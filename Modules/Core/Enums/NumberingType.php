<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum NumberingType: string implements LabeledEnum
{
    case CUSTOMER = 'Customer';
    case EXPENSE  = 'Expense';
    case INVOICE  = 'Invoice';
    case PAYMENT  = 'Payment';
    case PROJECT  = 'Project';
    case QUOTE    = 'Quote';
    case TASK     = 'Task';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER => 'Customer',
            self::EXPENSE  => 'Expense',
            self::INVOICE  => 'Invoice',
            self::PAYMENT  => 'Payment',
            self::PROJECT  => 'Project',
            self::QUOTE    => 'Quote',
            self::TASK     => 'Task',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CUSTOMER => 'primary',
            self::EXPENSE  => 'warning',
            self::INVOICE  => 'success',
            self::PAYMENT  => 'info',
            self::PROJECT  => 'secondary',
            self::QUOTE    => 'purple',
            self::TASK     => 'gray',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::CUSTOMER => 'CUS',
            self::EXPENSE  => 'EXP',
            self::INVOICE  => 'INV',
            self::PAYMENT  => 'PAY',
            self::PROJECT  => 'PRJ',
            self::QUOTE    => 'QUO',
            self::TASK     => 'TSK',
        };
    }
}
