<?php

namespace Modules\Subscriptions\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum SubscriptionStatus: string implements LabeledEnum
{
    case ACTIVE          = 'active';
    case TRIALING        = 'trialing';
    case IN_GRACE_PERIOD = 'in_grace_period';
    case PAUSED          = 'paused';
    case CANCELED        = 'canceled';
    case EXPIRED         = 'expired';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE          => 'Active',
            self::TRIALING        => 'Trialing',
            self::IN_GRACE_PERIOD => 'In Grace Period',
            self::PAUSED          => 'Paused',
            self::CANCELED        => 'Canceled',
            self::EXPIRED         => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE          => 'success',
            self::TRIALING        => 'info',
            self::IN_GRACE_PERIOD => 'warning',
            self::PAUSED          => 'gray',
            self::CANCELED        => 'danger',
            self::EXPIRED         => 'danger',
        };
    }

    public function badgeIcon(): string
    {
        return match ($this) {
            self::ACTIVE          => 'heroicon-o-check-circle',
            self::TRIALING        => 'heroicon-o-clock',
            self::IN_GRACE_PERIOD => 'heroicon-o-exclamation-triangle',
            self::PAUSED          => 'heroicon-o-pause-circle',
            self::CANCELED        => 'heroicon-o-x-circle',
            self::EXPIRED         => 'heroicon-o-minus-circle',
        };
    }
}
