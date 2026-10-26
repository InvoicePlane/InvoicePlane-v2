<?php

namespace Modules\Core\Traits;

trait HasOptions
{
    public static function options(bool $translate = true): array
    {
        $out = [];

        /* @phpstan-ignore-next-line */
        foreach (static::cases() as $case) {
            $label = method_exists($case, 'label')
                ? $case->label()
                : ucfirst(mb_strtolower($case->name));

            $out[$case->value] = $translate ? trans($label) : $label;
        }

        return $out;
    }
}
