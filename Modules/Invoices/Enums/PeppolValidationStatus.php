<?php

namespace Modules\Invoices\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Traits\HasOptions;

enum PeppolValidationStatus: string implements LabeledEnum
{
    use HasOptions;

    case VALID = 'valid';
    case INVALID = 'invalid';
    case NOT_FOUND = 'not_found';
    case ERROR = 'error';

    /**
     * Get the human-readable label for this Peppol validation status.
     *
     * @return string The label corresponding to the enum case.
     */
    public function label(): string
    {
        return match ($this) {
            self::VALID => 'Valid',
            self::INVALID => 'Invalid',
            self::NOT_FOUND => 'Not Found',
            self::ERROR => 'Error',
        };
    }

    /**
     * Get the UI color name associated with the Peppol validation status.
     *
     * @return string The color name: `'green'` for `VALID`, `'red'` for `INVALID` and `ERROR`, and `'orange'` for `NOT_FOUND`.
     */
    public function color(): string
    {
        return match ($this) {
            self::VALID => 'green',
            self::INVALID => 'red',
            self::NOT_FOUND => 'orange',
            self::ERROR => 'red',
        };
    }

    /**
     * Get the UI icon identifier for this Peppol validation status.
     *
     * @return string The icon identifier corresponding to the status (e.g. "heroicon-o-check-circle").
     */
    public function icon(): string
    {
        return match ($this) {
            self::VALID => 'heroicon-o-check-circle',
            self::INVALID => 'heroicon-o-x-circle',
            self::NOT_FOUND => 'heroicon-o-question-mark-circle',
            self::ERROR => 'heroicon-o-exclamation-triangle',
        };
    }
}