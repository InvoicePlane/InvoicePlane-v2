<?php

namespace Modules\Projects\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Traits\HasOptions;

enum TaskStatus: string implements LabeledEnum
{
    use HasOptions;
    case CANCELLED   = 'cancelled';
    case COMPLETED   = 'completed';
    case IN_PROGRESS = 'in_progress';
    case NOT_STARTED = 'not_started';
    case OPEN        = 'open';
    case PAID        = 'paid';

    /**
     * case NOT_STARTED = 1;
     * case IN_PROGRESS = 2;.
     *
     * case COMPLETE = 3;
     *
     * case PAID = 4;
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::CANCELLED   => trans('ip.task_status_cancelled'),
            self::IN_PROGRESS => trans('ip.task_status_in_progress'),
            self::NOT_STARTED => trans('ip.task_status_not_started'),
            self::PAID        => trans('ip.task_status_paid'),
            self::COMPLETED   => trans('ip.task_status_completed'),
            self::OPEN        => trans('ip.task_status_open'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CANCELLED   => 'warning',
            self::COMPLETED   => 'success',
            self::IN_PROGRESS => 'info',
            self::NOT_STARTED => 'gray',
            self::PAID        => 'emerald',
            self::OPEN        => 'info',
        };
    }
}
