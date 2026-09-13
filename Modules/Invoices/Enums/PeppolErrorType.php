<?php

namespace Modules\Invoices\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Traits\HasOptions;

enum PeppolErrorType: string implements LabeledEnum
{
    use HasOptions;

    case TRANSIENT = 'TRANSIENT';
    case PERMANENT = 'PERMANENT';
    case UNKNOWN   = 'UNKNOWN';

    /**
     * Get a human-readable label for the error type.
     *
     * @return string human-readable label for the enum case
     */
    public function label(): string
    {
        return match ($this) {
            self::TRANSIENT => 'Transient Error',
            self::PERMANENT => 'Permanent Error',
            self::UNKNOWN   => 'Unknown Error',
        };
    }

    /**
     * Gets the UI color identifier associated with this Peppol error type.
     *
     * @return string the color identifier: 'yellow' for TRANSIENT, 'red' for PERMANENT, 'gray' for UNKNOWN
     */
    public function color(): string
    {
        return match ($this) {
            self::TRANSIENT => 'yellow',
            self::PERMANENT => 'red',
            self::UNKNOWN   => 'gray',
        };
    }

    /**
     * Get the icon identifier corresponding to this error type.
     *
     * @return string the icon identifier for the enum case
     */
    public function icon(): string
    {
        return match ($this) {
            self::TRANSIENT => 'heroicon-o-arrow-path',
            self::PERMANENT => 'heroicon-o-x-circle',
            self::UNKNOWN   => 'heroicon-o-question-mark-circle',
        };
    }
}
