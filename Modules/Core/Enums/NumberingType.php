<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum NumberingType: string implements LabeledEnum
{
    case JOB      = 'Job';
    case JOB_CARD = 'JobCard';
    case PROJECT  = 'Project';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::JOB      => 'Job',
            self::JOB_CARD => 'Job Card',
            self::PROJECT  => 'Project',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::JOB      => 'primary',
            self::JOB_CARD => 'success',
            self::PROJECT  => 'info',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::JOB      => 'JOB',
            self::JOB_CARD => 'JC',
            self::PROJECT  => 'PRJ',
        };
    }
}
