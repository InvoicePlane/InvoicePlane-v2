<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum TaxRateType: string implements LabeledEnum
{
    case EXCLUSIVE = 'exclusive';
    case INCLUSIVE = 'inclusive';
    case ZERO      = 'zero';
    case EXEMPT    = 'exempt';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::EXCLUSIVE => trans('ip.tax_rate_type_exclusive'),
            self::INCLUSIVE => trans('ip.tax_rate_type_inclusive'),
            self::ZERO      => trans('ip.tax_rate_type_zero'),
            self::EXEMPT    => trans('ip.tax_rate_type_exempt'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EXCLUSIVE => 'gray',
            self::INCLUSIVE => 'info',
            self::ZERO      => 'warning',
            self::EXEMPT    => 'danger',
        };
    }
}
