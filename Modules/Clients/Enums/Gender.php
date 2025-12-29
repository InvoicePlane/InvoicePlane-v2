<?php

namespace Modules\Clients\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum Gender: string implements LabeledEnum
{
    case MALE    = 'male';
    case FEMALE  = 'female';
    case OTHER   = 'other';
    case UNKNOWN = 'unknown';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::MALE    => trans('ip.gender_male'),
            self::FEMALE  => trans('ip.gender_female'),
            self::OTHER   => trans('ip.gender_other'),
            self::UNKNOWN => trans('ip.gender_unknown'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MALE    => 'primary',
            self::FEMALE  => 'pink',
            self::OTHER   => 'gray',
            self::UNKNOWN => 'warning',
        };
    }
}
