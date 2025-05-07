<?php

namespace Modules\Currencies\Support;

class CurrencyConverterFactory
{
    public static function create()
    {
        $class = 'Modules\Currencies\Support\Drivers\\' . config('ip.currencyConversionDriver');

        return new $class();
    }
}
