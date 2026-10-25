<?php

namespace Modules\Projects\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Traits\HasOptions;

enum ProjectStatus: string implements LabeledEnum
{
    use HasOptions;
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
            self::ON_HOLD   => trans('enums.project_status.on_hold'),
            self::COMPLETED => trans('enums.project_status.completed'),
            self::PLANNED   => trans('enums.project_status.planned'),
            self::ACTIVE    => trans('enums.project_status.active'),
            self::CANCELLED => trans('enums.project_status.cancelled'),
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
