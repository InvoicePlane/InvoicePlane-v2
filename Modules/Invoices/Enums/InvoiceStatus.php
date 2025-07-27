<?php

namespace Modules\Invoices\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum InvoiceStatus: string implements LabeledEnum
{
    case DRAFT          = 'draft';
    case SENT           = 'sent';
    case VIEWED         = 'viewed';
    case PARTIALLY_PAID = 'paid';
    case PAID           = 'paid';
    case OVERDUE        = 'overdue';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT          => trans('ip.invoice_status_draft'),
            self::SENT           => trans('ip.invoice_status_sent'),
            self::VIEWED         => trans('ip.invoice_status_viewed'),
            self::PARTIALLY_PAID => trans('ip.invoice_status_partially_paid'),
            self::PAID           => trans('ip.invoice_status_paid'),
            self::OVERDUE        => trans('ip.invoice_status_overdue'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT          => 'gray',
            self::SENT           => 'emerald',
            self::VIEWED         => 'info',
            self::PARTIALLY_PAID => 'warning',
            self::PAID           => 'green',
            self::OVERDUE        => 'maroon',
        };
    }
}
