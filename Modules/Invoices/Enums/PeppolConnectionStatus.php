<?php

namespace Modules\Invoices\Enums;

use Modules\Core\Contracts\LabeledEnum;
use Modules\Core\Traits\HasOptions;

enum PeppolConnectionStatus: string implements LabeledEnum
{
    use HasOptions;

    case UNTESTED = 'untested';
    case SUCCESS = 'success';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::UNTESTED => 'Untested',
            self::SUCCESS => 'Success',
            self::FAILED => 'Failed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UNTESTED => 'gray',
            self::SUCCESS => 'green',
            self::FAILED => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::UNTESTED => 'heroicon-o-question-mark-circle',
            self::SUCCESS => 'heroicon-o-check-circle',
            self::FAILED => 'heroicon-o-x-circle',
        };
    }
}
