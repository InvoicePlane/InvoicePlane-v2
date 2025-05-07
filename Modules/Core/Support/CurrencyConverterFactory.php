<?php

namespace Modules\Core\Support;

class CurrencyConverterFactory
{
    public static function create()
    {
        $class = 'Modules\Core\Support\Drivers\\' . config('ip.currencyConversionDriver');

        return new $class();
    }
}
