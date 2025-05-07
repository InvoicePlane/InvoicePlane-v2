<?php

namespace Modules\Core\Enums;

use Modules\Core\Enums\TaxRateType;

use Modules\Core\Contracts\LabeledEnum;

enum TaxRateType: string implements \Modules\Core\Contracts\LabeledEnum
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
            self::EXCLUSIVE => 'Exclusive',
            self::INCLUSIVE => 'Inclusive',
            self::ZERO      => 'Zero Rated',
            self::EXEMPT    => 'Exempt',
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
