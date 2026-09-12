<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum ReportGroupBy: string implements LabeledEnum
{
    case CATEGORY = 'category';
    case TAX_RATE = 'tax_rate';
    case PRODUCT  = 'product';
    case SKU      = 'sku';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::CATEGORY => trans('ip.group_by_category'),
            self::TAX_RATE => trans('ip.group_by_tax_rate'),
            self::PRODUCT  => trans('ip.group_by_product'),
            self::SKU      => trans('ip.group_by_sku'),
        };
    }

    public function color(): string
    {
        return 'primary';
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}
