<?php

namespace Modules\Core\Enums;

use Modules\Core\Contracts\LabeledEnum;

enum GroupOptions: string implements LabeledEnum
{
    public function resetNumberOptions()
    {
        return [
            '0' => trans('ip.never'),
            '1' => trans('ip.yearly'),
            '2' => trans('ip.monthly'),
            '3' => trans('ip.weekly'),
        ];
    }

    public function label(): string
    {
        // TODO: Implement label() method.
    }

    public function color(): string
    {
        // TODO: Implement color() method.
    }
}
