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

/**

    case DRAFT = 1;

    case SENT = 2;

    case VIEWED = 3;

    case APPROVED = 4;

    case REJECTED = 5;

    case CANCELED = 6;
*/


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT    => 'ip.draft',
            self::SENT     => 'ip.sent',
            self::VIEWED   => 'ip.viewed',
            self::APPROVED => 'ip.approved',
            self::CANCELED => 'ip.canceled',
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
