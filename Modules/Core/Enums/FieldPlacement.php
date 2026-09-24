<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum FieldPlacement: string implements LabeledEnum
{
    case HIDDEN        = 'hidden';
    case INLINE_COLUMN = 'inline_column';
    case BELOW_ROW     = 'below_row';

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
            self::HIDDEN        => trans('ip.placement_hidden'),
            self::INLINE_COLUMN => trans('ip.placement_inline_column'),
            self::BELOW_ROW     => trans('ip.placement_below_row'),
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
