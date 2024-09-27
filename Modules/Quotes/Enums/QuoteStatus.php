<?php

namespace Modules\Quotes\Enums;

enum QuoteStatus: int
{
    case DRAFT = 1;

    case SENT = 2;

    case VIEWED = 3;

    case APPROVED = 4;

    case REJECTED = 5;

    case CANCELED = 6;

    public function getLabel(): string
    {
        return match($this) {
            self::DRAFT    => 'ip.draft',
            self::SENT     => 'ip.sent',
            self::VIEWED   => 'ip.viewed',
            self::APPROVED => 'ip.approved',
            self::REJECTED => 'ip.rejected',
            self::CANCELED => 'ip.canceled',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::DRAFT    => 'gray',
            self::SENT     => 'blue',
            self::VIEWED   => 'lightgreen',
            self::APPROVED => 'darkgreen',
            self::REJECTED => 'red',
            self::CANCELED => 'maroon',
        };
    }
}
