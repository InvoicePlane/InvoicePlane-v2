<?php

namespace Modules\Projects\Enums;

use Modules\Projects\Enums\ProjectStatus;

use Modules\Core\Contracts\LabeledEnum;

enum ProjectStatus: string implements \Modules\Core\Contracts\LabeledEnum
{
    case PLANNED   = 'planned';
    case ACTIVE    = 'active';
    case COMPLETED = 'completed';
    case ON_HOLD   = 'on_hold';
    case CANCELLED = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::ON_HOLD   => 'On Hold',
            self::COMPLETED => 'Completed',
            self::PLANNED   => 'Planned',
            self::ACTIVE    => 'Active',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ON_HOLD   => 'warning',
            self::COMPLETED => 'success',
            self::PLANNED   => 'info',
            self::ACTIVE    => 'green',
            self::CANCELLED => 'danger',
        };
    }
}
