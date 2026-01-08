<?php

namespace Modules\Core\Support;

class Frequency
{
    public static function lists()
    {
        return [
            '1' => trans('ip.days'),
            '2' => trans('ip.weeks'),
            '3' => trans('ip.months'),
            '4' => trans('ip.years'),
        ];
    }
}
