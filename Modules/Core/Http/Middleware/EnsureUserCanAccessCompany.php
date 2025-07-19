<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Core\Models\Company;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessCompany
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

        if ($company instanceof Company) {
            if ($user->hasAnyRole(\Modules\Core\Enums\UserRole::elevated())) {
                return $this->setCurrentCompany($company, $request, $next);
            }

            if ( ! $user->companies->contains('id', $company->id)) {
                abort(403, 'You do not have access to this company.');
            }

            return $this->setCurrentCompany($company, $request, $next);
        }

        if ($user->companies->isEmpty() && ! $user->hasAnyRole(\Modules\Core\Enums\UserRole::elevated())) {
            abort(403, 'You do not have access to any companies.');
        }

        $company = session('current_company_id')
            ? Company::query()->find(session('current_company_id'))
            : $user->companies->first();

        if ($company) {
            return $this->setCurrentCompany($company, $request, $next);
        }

        return $next($request);
    }

    protected function setCurrentCompany(Company $company, Request $request, Closure $next): Response
    {
        session(['current_company_id' => $company->id]);

        if ($request->route() && $request->route()->hasParameter('tenant')) {
            $request->route()->setParameter('tenant', Str::lower($company->search_code));
        }

        return $next($request);
    }
}
