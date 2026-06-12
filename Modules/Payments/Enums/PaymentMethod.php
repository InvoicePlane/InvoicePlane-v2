<?php

namespace Modules\Payments\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum PaymentMethod: string implements LabeledEnum
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
            self::BANK_TRANSFER => trans('ip.payment_method_bank_transfer'),
            self::CASH          => trans('ip.payment_method_cash'),
            self::CREDIT_CARD   => trans('ip.payment_method_credit_card'),
            self::PAYPAL        => trans('ip.payment_method_paypal'),
            self::STRIPE        => trans('ip.payment_method_stripe'),
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
