<?php

namespace Modules\Payments\Enums;

use Exception;

enum PaymentStatus: string
{
    public const STATUS_REFUNDED_PARTIALLY = 'ip.partially_refunded';

    public const STATUS_FAILED = 'ip.failed';

    public function getLabel(): string
    {
        return match($this) {
            self::STATUS_REFUNDED_PARTIALLY => 'ip.partially_refunded',
            self::STATUS_FAILED             => 'ip.failed',
            default                         => throw new Exception('Unexpected match value'),
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::STATUS_REFUNDED_PARTIALLY => 'warning',
            self::STATUS_FAILED             => 'maroon',
            default                         => 'gray',
        };
    }
}
