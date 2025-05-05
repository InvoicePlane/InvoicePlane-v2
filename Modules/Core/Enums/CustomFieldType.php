<?php

namespace Modules\Core\Enums;

enum CustomFieldType: string implements \Modules\Core\Contracts\LabeledEnum
{
    case TEXT    = 'text';
    case NUMBER  = 'number';
    case DATE    = 'date';
    case BOOLEAN = 'boolean';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::TEXT    => 'Single Line Text',
            self::NUMBER  => 'Multi Line Text',
            self::DATE    => 'Date Picker',
            self::BOOLEAN => 'Boolean',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TEXT    => 'gray',
            self::NUMBER  => 'info',
            self::DATE    => 'warning',
            self::BOOLEAN => 'success',
        };
    }
}
