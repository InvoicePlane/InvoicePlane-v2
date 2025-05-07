<?php

namespace Modules\Core\Enums;

use Modules\Core\Enums\AddressType;

use Modules\Core\Contracts\LabeledEnum;

enum AddressType: string implements \Modules\Core\Contracts\LabeledEnum
{
    case BILLING  = 'billing';
    case SHIPPING = 'shipping';
    case OFFICE   = 'office';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::BILLING  => 'Billing',
            self::SHIPPING => 'Shipping',
            self::OFFICE   => 'Office',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BILLING  => 'info',
            self::SHIPPING => 'success',
            self::OFFICE   => 'gray',
        };
    }
}
