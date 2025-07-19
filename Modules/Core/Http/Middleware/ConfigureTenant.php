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

        $company = $request->route('tenant');

        if (is_string($company)) {
            $company = Company::query()->where('search_code', $company)->first();
        }

        if ( ! $company && $request->has('tenant')) {
            $company = Company::query()->where('search_code', Str::upper($request->query('tenant')))->first();
        }

        if ( ! $company && session()->has('current_company_id')) {
            $company = Company::query()->find(session('current_company_id'));
        }

        if ( ! $company) {
            $company = $user->companies->first() ?? Company::query()->first();
        }

        if ($company) {
            session(['current_company_id' => $company->id]);
            view()->share('currentCompany', $company);
        }

        return $next($request);
    }
}
