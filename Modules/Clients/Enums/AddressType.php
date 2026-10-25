<?php

namespace Modules\Clients\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum AddressType: string implements LabeledEnum
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
            self::BILLING  => trans('enums.address_type.billing'),
            self::SHIPPING => trans('enums.address_type.shipping'),
            self::OFFICE   => trans('enums.address_type.office'),
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
