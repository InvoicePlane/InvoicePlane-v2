<?php

namespace App\Support;

class FileNames
{
    public static function invoice($invoice)
    {
        return trans('ip.invoice') . '_' . str_replace('/', '-', $invoice->number) . '.pdf';
    }

    public static function quote($quote)
    {
        return trans('ip.quote') . '_' . str_replace('/', '-', $quote->number) . '.pdf';
    }
}
