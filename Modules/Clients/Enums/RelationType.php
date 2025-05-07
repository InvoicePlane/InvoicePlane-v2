<?php

namespace Modules\Clients\Enums;

use Modules\Clients\Enums\RelationType;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Contracts\LabeledEnum;

enum RelationType: string implements \Modules\Core\Contracts\LabeledEnum
{
    case CUSTOMER = 'customer';
    case LEAD     = 'lead';
    case PARTNER  = 'partner';
    case PROSPECT = 'prospect';
    case VENDOR   = 'vendor';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER => 'Customer',
            self::VENDOR   => 'Vendor',
            self::PROSPECT => 'Prospect',
            self::PARTNER  => 'Partner',
            self::LEAD     => 'Lead',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CUSTOMER => 'primary',
            self::VENDOR   => 'info',
            self::PROSPECT => 'warning',
            self::PARTNER  => 'success',
            self::LEAD     => 'green',
        };
    }
}
