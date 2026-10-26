<?php

namespace Modules\Quotes\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum QuoteStatus: string implements LabeledEnum
{
    case DRAFT     = 'draft';
    case SENT      = 'sent';
    case VIEWED    = 'viewed';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';
    case CONVERTED = 'converted';

    /**
     * case DRAFT = 1;.
     *
     * case SENT = 2;
     *
     * case VIEWED = 3;
     *
     * case APPROVED = 4;
     *
     * case REJECTED = 5;
     *
     * case CANCELED = 6;
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => trans('ip.quote_status_draft'),
            self::SENT      => trans('ip.quote_status_sent'),
            self::VIEWED    => trans('ip.quote_status_viewed'),
            self::APPROVED  => trans('ip.quote_status_approved'),
            self::REJECTED  => trans('ip.quote_status_rejected'),
            self::CONVERTED => trans('ip.quote_status_converted'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT     => 'gray',
            self::SENT      => 'green',
            self::VIEWED    => 'info',
            self::APPROVED  => 'success',
            self::REJECTED  => 'danger',
            self::CONVERTED => 'warning',
        };
    }
}
