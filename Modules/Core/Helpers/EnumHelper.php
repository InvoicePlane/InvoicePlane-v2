<?php

namespace Modules\Core\Helpers;

use BackedEnum;

class EnumHelper
{
    public static function safeEnum(string $enumClass, mixed $value): ?BackedEnum
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

        if (is_subclass_of($enumClass, BackedEnum::class)) {
            return $enumClass::tryFrom($value);
        }

        return null;
    }
}
