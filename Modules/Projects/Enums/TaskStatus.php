<?php

namespace Modules\Projects\Enums;

enum TaskStatus: string implements \Modules\Core\Contracts\LabeledEnum
{
    case OPEN        = 'open';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';
    case CANCELLED   = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED   => 'Completed',
            self::OPEN        => 'Open',
            self::CANCELLED   => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'info',
            self::COMPLETED   => 'success',
            self::OPEN        => 'gray',
            self::CANCELLED   => 'warning',
        };
    }
}
