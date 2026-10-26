<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Core\Models\Company;
use Symfony\Component\HttpFoundation\Response;

class ConfigureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ( ! $user) {
            return $next($request);
        }

        // Get the current tenant from the route or query string
        $company = $request->route('tenant');

        // If company is a search_code, find the company
        if (is_string($company)) {
            $company = Company::query()->where('search_code', Str::lower($company))->first();
        }

        // If no company from route, check query string
        if ( ! $company && $request->has('tenant')) {
            $company = Company::query()->where('search_code', Str::lower($request->query('tenant')))->first();
        }

        // If still no company, use the one from session
        if ( ! $company && session()->has('current_company_id')) {
            $company = Company::query()->find(session('current_company_id'));
        }

        // Last resort: get user's first company or any company
        if ( ! $company) {
            $company = $user->companies->first() ?? Company::query()->first();
        }

        // Set the company in session if we found one
        if ($company) {
            session(['current_company_id' => $company->id]);
            view()->share('currentCompany', $company);
        }

        return $next($request);
    }
}
