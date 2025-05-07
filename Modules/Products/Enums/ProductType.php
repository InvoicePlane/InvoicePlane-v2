<?php

namespace Modules\Products\Enums;

use Modules\Expenses\Models\Expense;

use Modules\Products\Enums\ProductType;

use Modules\Products\Models\Product;

use Modules\Core\Contracts\LabeledEnum;

enum ProductType: string implements \Modules\Core\Contracts\LabeledEnum
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
            self::PRODUCT  => 'Product',
            self::SERVICE  => 'Service',
            self::HOURS    => 'Billable Hours',
            self::PACKAGE  => 'Package',
            self::DOWNLOAD => 'Download',
            self::EXPENSE  => 'Expense',
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
