<?php

namespace Modules\Clients\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum RelationType: string implements LabeledEnum
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
            self::CUSTOMER => trans('ip.customer'),
            self::VENDOR   => trans('ip.vendor'),
            self::PROSPECT => trans('ip.prospect'),
            self::PARTNER  => trans('ip.partner'),
            self::LEAD     => trans('ip.lead'),
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
