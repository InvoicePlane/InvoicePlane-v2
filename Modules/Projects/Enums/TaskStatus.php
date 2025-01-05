<?php

namespace Modules\Projects\Enums;

enum TaskStatus: int
{
    case NOT_STARTED = 1;

    case IN_PROGRESS = 2;

    case COMPLETE = 3;

    case PAID = 4;

    public function getLabel(): string
    {
        return match($this) {
            self::NOT_STARTED => 'ip.not_started',
            self::IN_PROGRESS => 'ip.in_progress',
            self::COMPLETE    => 'ip.complete',
            self::PAID        => 'ip.paid',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::NOT_STARTED => 'gray',
            self::IN_PROGRESS => 'warning',
            self::COMPLETE    => 'success',
            self::PAID        => 'emerald-400',
        };
    }
}
