<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum ModelType: string implements LabeledEnum
{
    case INVOICE = 'invoice';
    case QUOTE   = 'quote';
    case CLIENT  = 'client';
    case PAYMENT = 'payment';
    case PROJECT = 'project';
    case TASK    = 'task';
    case EXPENSE = 'expense';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromString(string $type): self
    {
        return match (mb_strtolower($type)) {
            'invoice' => self::INVOICE,
            'quote'   => self::QUOTE,
            'client'  => self::CLIENT,
            'payment' => self::PAYMENT,
            'project' => self::PROJECT,
            'task'    => self::TASK,
            'expense' => self::EXPENSE,
            default   => self::INVOICE,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::INVOICE => trans('ip.invoice'),
            self::QUOTE   => trans('ip.quote'),
            self::CLIENT  => trans('ip.client'),
            self::PAYMENT => trans('ip.payment'),
            self::PROJECT => trans('ip.project'),
            self::TASK    => trans('ip.task'),
            self::EXPENSE => trans('ip.expense'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INVOICE => 'success',
            self::QUOTE   => 'info',
            self::CLIENT  => 'primary',
            self::PAYMENT => 'warning',
            self::PROJECT => 'purple',
            self::TASK    => 'indigo',
            self::EXPENSE => 'danger',
        };
    }

    public function getModelClass(): string
    {
        return match ($this) {
            self::INVOICE => 'Modules\\Invoices\\Models\\Invoice',
            self::QUOTE   => 'Modules\\Quotes\\Models\\Quote',
            self::CLIENT  => 'Modules\\Clients\\Models\\Relation',
            self::PAYMENT => 'Modules\\Payments\\Models\\Payment',
            self::PROJECT => 'Modules\\Projects\\Models\\Project',
            self::TASK    => 'Modules\\Projects\\Models\\Task',
            self::EXPENSE => 'Modules\\Expenses\\Models\\Expense',
        };
    }
}
