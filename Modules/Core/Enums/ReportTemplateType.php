<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum ReportTemplateType: string implements LabeledEnum
{
    case INVOICE = 'invoice';
    case QUOTE   = 'quote';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::INVOICE => trans('ip.invoice'),
            self::QUOTE   => trans('ip.quote'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INVOICE => 'success',
            self::QUOTE   => 'info',
        };
    }
}
