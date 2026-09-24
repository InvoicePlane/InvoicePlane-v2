<?php

namespace Modules\Core\Enums;

enum ReportBlockWidth: string
{
    case ONE_THIRD  = 'one_third';
    case HALF       = 'half';
    case TWO_THIRDS = 'two_thirds';
    case FULL       = 'full';

    /**
     * Get the grid width for this block width (in 12-column grid system).
     */
    public function getGridWidth(): int
    {
        return match ($this) {
            self::ONE_THIRD  => 4,
            self::HALF       => 6,
            self::TWO_THIRDS => 8,
            self::FULL       => 12,
        };
    }
}
