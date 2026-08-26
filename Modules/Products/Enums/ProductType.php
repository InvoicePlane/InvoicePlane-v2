<?php

namespace Modules\Products\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum ProductType: string implements LabeledEnum
{
    case PRODUCT  = 'product';
    case EXPENSE  = 'expense';
    case SERVICE  = 'service';
    case HOURS    = 'hours';
    case PACKAGE  = 'package';
    case DOWNLOAD = 'download';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::PRODUCT  => trans('ip.product'),
            self::SERVICE  => trans('ip.service'),
            self::HOURS    => trans('ip.billable_hours'),
            self::PACKAGE  => trans('ip.package'),
            self::DOWNLOAD => trans('ip.download'),
            self::EXPENSE  => trans('ip.expense'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PRODUCT  => 'gray',
            self::SERVICE  => 'info',
            self::HOURS    => 'warning',
            self::PACKAGE  => 'yellow',
            self::DOWNLOAD => 'green',
            self::EXPENSE  => 'maroon',
        };
    }
}
