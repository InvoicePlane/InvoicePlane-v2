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

    public function label(): string
    {
        return match ($this) {
            self::VALID => 'Valid',
            self::INVALID => 'Invalid',
            self::NOT_FOUND => 'Not Found',
            self::ERROR => 'Error',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::VALID => 'green',
            self::INVALID => 'red',
            self::NOT_FOUND => 'orange',
            self::ERROR => 'red',
        };
    }

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
