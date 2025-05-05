<?php

namespace Modules\Core\Enums;

enum Gender: string implements \Modules\Core\Contracts\LabeledEnum
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
            self::MALE    => 'Male',
            self::FEMALE  => 'Female',
            self::OTHER   => 'Other',
            self::UNKNOWN => 'Unknown',
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
