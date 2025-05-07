<?php

namespace Modules\Payments\Enums;

use Modules\Payments\Models\PaymentMethod;

use Modules\Core\Support\Results\Payments;

use Modules\Core\Contracts\LabeledEnum;

enum PaymentMethod: string implements \Modules\Core\Contracts\LabeledEnum
{
    case BANK_TRANSFER = 'bank_transfer';
    case CASH          = 'cash';
    case CREDIT_CARD   = 'credit_card';
    case PAYPAL        = 'paypal';
    case STRIPE        = 'stripe';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CASH          => 'Cash',
            self::CREDIT_CARD   => 'Credit Card',
            self::PAYPAL        => 'PayPal',
            self::STRIPE        => 'Stripe',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'gray',
            self::CASH          => 'warning',
            self::CREDIT_CARD   => 'info',
            self::PAYPAL        => 'primary',
            self::STRIPE        => 'success',
        };
    }
}
