<?php

namespace Modules\Invoices\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Traits\HasOptions;

enum PeppolConnectionStatus: string implements LabeledEnum
{
    use HasOptions;

    case UNTESTED = 'untested';
    case SUCCESS  = 'success';
    case FAILED   = 'failed';

    /**
     * Returns a human-readable label for the connection status.
     *
     * @return string the label for the enum case: 'Untested', 'Success', or 'Failed'
     */
    public function label(): string
    {
        return match ($this) {
            self::UNTESTED => 'Untested',
            self::SUCCESS  => 'Success',
            self::FAILED   => 'Failed',
        };
    }

    /**
     * The display color name for the Peppol connection status.
     *
     * @return string the color name for the status: 'gray' for UNTESTED, 'green' for SUCCESS, 'red' for FAILED
     */
    public function color(): string
    {
        return match ($this) {
            self::UNTESTED => 'gray',
            self::SUCCESS  => 'green',
            self::FAILED   => 'red',
        };
    }

    /**
     * Get the icon identifier associated with the current status.
     *
     * @return string the icon identifier corresponding to the enum case
     */
    public function icon(): string
    {
        return match ($this) {
            self::UNTESTED => 'heroicon-o-question-mark-circle',
            self::SUCCESS  => 'heroicon-o-check-circle',
            self::FAILED   => 'heroicon-o-x-circle',
        };
    }
}
