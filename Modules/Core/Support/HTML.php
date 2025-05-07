<?php

namespace Modules\Core\Support;

use Modules\Core\Support\HTML;

use Modules\Core\Events\InvoiceHTMLCreating;
use Modules\Core\Events\QuoteHTMLCreating;

class HTML
{
    public static function invoice($invoice)
    {
        app()->setLocale($invoice->customer->language);

        config(['ip.baseCurrency' => $invoice->currency_code]);

        event(new InvoiceHTMLCreating($invoice));

        $template = str_replace('.blade.php', '', $invoice->template);

        if (view()->exists('invoice_templates.' . $template)) {
            $template = 'invoice_templates.' . $template;
        } else {
            $template = 'templates.invoices.default';
        }

        return view($template)
            ->with('invoice', $invoice)
            ->with('logo', $invoice->companyProfile->logo())->render();
    }

    public static function quote($quote)
    {
        app()->setLocale($quote->customer->language);

        config(['ip.baseCurrency' => $quote->currency_code]);

        event(new QuoteHTMLCreating($quote));

        $template = str_replace('.blade.php', '', $quote->template);

        if (view()->exists('quote_templates.' . $template)) {
            $template = 'quote_templates.' . $template;
        } else {
            $template = 'templates.quotes.default';
        }

        return view($template)
            ->with('quote', $quote)
            ->with('logo', $quote->companyProfile->logo())->render();
    }
}
