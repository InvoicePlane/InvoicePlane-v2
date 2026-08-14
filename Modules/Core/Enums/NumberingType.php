<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum NumberingType: string implements LabeledEnum
{
    case CUSTOMER     = 'Customer';
    case EXPENSE      = 'Expense';
    case INVOICE      = 'Invoice';
    case PAYMENT      = 'Payment';
    case PROJECT      = 'Project';
    case QUOTE        = 'Quote';
    case SUBSCRIPTION = 'Subscription';
    case TASK         = 'Task';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER     => trans('ip.customer'),
            self::EXPENSE      => trans('ip.expense'),
            self::INVOICE      => trans('ip.invoice'),
            self::PAYMENT      => trans('ip.payment'),
            self::PROJECT      => trans('ip.project'),
            self::QUOTE        => trans('ip.quote'),
            self::SUBSCRIPTION => trans('ip.subscription'),
            self::TASK         => trans('ip.task'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CUSTOMER     => 'primary',
            self::EXPENSE      => 'warning',
            self::INVOICE      => 'success',
            self::PAYMENT      => 'info',
            self::PROJECT      => 'secondary',
            self::QUOTE        => 'purple',
            self::SUBSCRIPTION => 'teal',
            self::TASK         => 'gray',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::CUSTOMER     => 'CUS',
            self::EXPENSE      => 'EXP',
            self::INVOICE      => 'INV',
            self::PAYMENT      => 'PAY',
            self::PROJECT      => 'PRJ',
            self::QUOTE        => 'QUO',
            self::SUBSCRIPTION => 'SUB',
            self::TASK         => 'TSK',
        };
    }
}
