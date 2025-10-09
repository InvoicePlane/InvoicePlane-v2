<?php

namespace Modules\Invoices\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Traits\HasOptions;

enum PeppolErrorType: string implements LabeledEnum
{
    use HasOptions;

    case TRANSIENT = 'TRANSIENT';
    case PERMANENT = 'PERMANENT';
    case UNKNOWN = 'UNKNOWN';

    public function label(): string
    {
        return match ($this) {
            self::TRANSIENT => 'Transient Error',
            self::PERMANENT => 'Permanent Error',
            self::UNKNOWN => 'Unknown Error',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TRANSIENT => 'yellow',
            self::PERMANENT => 'red',
            self::UNKNOWN => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TRANSIENT => 'heroicon-o-arrow-path',
            self::PERMANENT => 'heroicon-o-x-circle',
            self::UNKNOWN => 'heroicon-o-question-mark-circle',
        };
    }
}
