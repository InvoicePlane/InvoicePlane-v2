<?php

namespace Modules\Quotes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Localizable;
use Modules\Core\Support\DocumentFormatter;
use Modules\Quotes\Models\Quote;
use Symfony\Component\HttpFoundation\Response;

class SetQuoteLocale
{
    use Localizable;

    public function handle(Request $request, Closure $next): Response
    {
        $quote = $request->route('quote');
        if ( ! $quote instanceof Quote) {
            return $next($request);
        }

        return $this->withLocale(
            DocumentFormatter::resolveLocale($quote->prospect?->language),
            fn () => $next($request),
        );
    }
}
