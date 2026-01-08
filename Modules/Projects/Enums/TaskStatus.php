<?php

namespace Modules\Projects\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum TaskStatus: string implements LabeledEnum
{
    case CANCELLED   = 'cancelled';
    case COMPLETE    = 'complete';
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
            self::CANCELLED   => 'ip.cancelled',
            self::COMPLETE    => 'ip.complete',
            self::IN_PROGRESS => 'ip.in_progress',
            self::NOT_STARTED => 'ip.not_started',
            self::PAID        => 'ip.paid',
            self::COMPLETED   => 'ip.completed',
            self::OPEN        => 'ip.open',
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
            self::COMPLETE    => 'success',
            self::OPEN        => 'info',
        };
    }
}
