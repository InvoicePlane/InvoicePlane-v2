<?php

namespace Modules\Subscriptions\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum BillingInterval: string implements LabeledEnum
{
    case WEEKLY  = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY  = 'yearly';
    case CUSTOM  = 'custom';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::WEEKLY  => trans('ip.billing_interval_weekly'),
            self::MONTHLY => trans('ip.billing_interval_monthly'),
            self::YEARLY  => trans('ip.billing_interval_yearly'),
            self::CUSTOM  => trans('ip.billing_interval_custom'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WEEKLY  => 'info',
            self::MONTHLY => 'primary',
            self::YEARLY  => 'success',
            self::CUSTOM  => 'warning',
        };
    }
}
