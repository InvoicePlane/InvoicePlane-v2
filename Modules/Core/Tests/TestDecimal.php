<?php

namespace Modules\Core\Tests;

class TestDecimal
{
    public static function exact(float|string $amount, int $precision = 4): string
    {
        return number_format((float) $amount, $precision, '.', '');
    }
}
