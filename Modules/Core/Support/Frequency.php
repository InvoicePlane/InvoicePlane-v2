<?php

namespace Modules\Core\Support;

use Modules\Core\Support\Frequency;

class Frequency
{
    /**
     * Returns a list of frequencies for recurring invoices.
     *
     * @return array
     */
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
