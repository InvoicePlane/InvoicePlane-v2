<?php

namespace Modules\Payments\Enums;

enum PaymentStatus: string
{
    case REFUNDED_PARTIALLY = 'ip.partially_refunded';
    case FAILED = 'ip.failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::REFUNDED_PARTIALLY => 'ip.partially_refunded',
            self::FAILED             => 'ip.failed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::REFUNDED_PARTIALLY => 'warning',
            self::FAILED             => 'maroon',
            default                  => 'gray',
        };
    }
}
