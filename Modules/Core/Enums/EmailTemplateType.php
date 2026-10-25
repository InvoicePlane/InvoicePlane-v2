<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum EmailTemplateType: string implements LabeledEnum
{
    //'invoice', 'quote', 'payment', 'reminder'

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
            self::TEXT    => trans('enums.email_template_type.text'),
            self::NUMBER  => trans('enums.email_template_type.number'),
            self::DATE    => trans('enums.email_template_type.date'),
            self::BOOLEAN => trans('enums.email_template_type.boolean'),
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
