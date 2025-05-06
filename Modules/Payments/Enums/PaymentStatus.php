<?php

namespace Modules\Payments\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum PaymentStatus: string implements LabeledEnum
{
    case PENDING   = 'pending';
    case COMPLETED = 'completed';
    case FAILED    = 'failed';
    case REFUNDED  = 'refunded';

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
            self::REFUNDED_PARTIALLY => 'ip.partially_refunded',
            self::PENDING            => 'Pending',
            self::COMPLETED          => 'Completed',
            self::FAILED             => 'ip.failed',
            self::REFUNDED           => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING            => 'warning',
            self::COMPLETED          => 'success',
            self::FAILED             => 'danger',
            self::REFUNDED           => 'gray',
            self::REFUNDED_PARTIALLY => 'silver',
        };
    }
}
