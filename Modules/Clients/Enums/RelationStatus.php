<?php

namespace Modules\Clients\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum RelationStatus: string implements LabeledEnum
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => trans('ip.active'),
            self::INACTIVE => trans('ip.inactive'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE   => 'primary',
            self::INACTIVE => 'warning',
        };
    }
}
