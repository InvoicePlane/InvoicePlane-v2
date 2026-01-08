<?php

namespace Modules\Invoices\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum InvoiceStatus: string implements LabeledEnum
{
    case DRAFT   = 'draft';
    case SENT    = 'sent';
    case VIEWED  = 'viewed';
    case PAID    = 'paid';
    case OVERDUE = 'overdue';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT   => 'Draft',
            self::SENT    => 'Sent',
            self::VIEWED  => 'Viewed',
            self::PAID    => 'Paid',
            self::OVERDUE => 'Overdue',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT   => 'gray',
            self::SENT    => 'emerald',
            self::VIEWED  => 'info',
            self::PAID    => 'green',
            self::OVERDUE => 'maroon',
        };
    }
}
