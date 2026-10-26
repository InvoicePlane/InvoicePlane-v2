<?php

namespace Modules\Payments\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum PaymentStatus: string implements LabeledEnum
{
    case COMPLETED          = 'completed';
    case FAILED             = 'failed';
    case PENDING            = 'pending';
    case REFUNDED           = 'refunded';
    case REFUNDED_PARTIALLY = 'partially_refunded';

    /**
     * case REFUNDED_PARTIALLY = 'ip.partially_refunded';
     * case FAILED = 'ip.failed';.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::COMPLETED          => trans('enums.payment_status.completed'),
            self::FAILED             => trans('enums.payment_status.failed'),
            self::PENDING            => trans('enums.payment_status.pending'),
            self::REFUNDED           => trans('enums.payment_status.refunded'),
            self::REFUNDED_PARTIALLY => trans('enums.payment_status.refunded_partially'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::COMPLETED          => 'success',
            self::FAILED             => 'danger',
            self::PENDING            => 'warning',
            self::REFUNDED           => 'gray',
            self::REFUNDED_PARTIALLY => 'silver',
        };
    }
}
