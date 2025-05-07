<?php

namespace Modules\Core\Helpers;

use Modules\Core\Contracts\LabeledEnum;

class EnumHelper
{
    public static function safeEnum(string $enumClass, mixed $value): ?LabeledEnum
    {
        if ( ! enum_exists($enumClass)) {
            return null;
        }

        if ($value instanceof $enumClass) {
            return $value;
        }

        if (blank($value)) {
            return null;
        }

        return $enumClass::tryFrom($value);
    }
}
