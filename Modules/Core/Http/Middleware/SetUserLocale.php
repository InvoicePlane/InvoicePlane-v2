<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $language = $request->user()?->language;

        if (is_string($language) && array_key_exists($language, config('languages', []))) {
            app()->setLocale($language);
        }

        // Keep the locale for component rendering after Livewire replays this middleware.
        return $next($request);
    }
}
