<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum NumberingType: string implements LabeledEnum
{
    case PROJECT = 'Project';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::PROJECT => 'Project',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PROJECT => 'info',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::PROJECT => 'PRJ',
        };
    }
}
