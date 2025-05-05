<?php

namespace Modules\Projects\Enums;

enum TaskStatus: string implements \Modules\Core\Contracts\LabeledEnum
{
    case OPEN        = 'open';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';
    case CANCELLED   = 'cancelled';
    case PAID        = 'paid';

/**
    case NOT_STARTED = 1;

    case IN_PROGRESS = 2;

    case COMPLETE = 3;

    case PAID = 4;
*/


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'ip.not_started',
            self::IN_PROGRESS => 'ip.in_progress',
            self::COMPLETE    => 'ip.complete',
            self::PAID        => 'ip.paid',
            self::CANCELLED   => 'ip.cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'info',
            self::COMPLETED   => 'success',
            self::NOT_STARTED => 'gray',
            self::PAID        => 'emerald',
            self::CANCELLED   => 'warning',
        };
    }
}
