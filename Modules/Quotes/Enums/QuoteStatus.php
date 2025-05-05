<?php

namespace Modules\Quotes\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum QuoteStatus: string implements LabeledEnum
{
    case DRAFT    = 'draft';
    case SENT     = 'sent';
    case VIEWED   = 'viewed';
    case APPROVED = 'approved';
    case CANCELED = 'canceled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT    => 'Draft',
            self::SENT     => 'Sent',
            self::VIEWED   => 'Viewed',
            self::APPROVED => 'Approved',
            self::CANCELED => 'Canceled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT    => 'gray',
            self::SENT     => 'green',
            self::VIEWED   => 'info',
            self::APPROVED => 'success',
            self::CANCELED => 'danger',
        };
    }
}
