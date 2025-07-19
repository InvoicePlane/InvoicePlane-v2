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

        // If no user is authenticated, allow the request to continue
        if ( ! $user) {
            return $next($request);
        }

        // Get company from route or query string
        $company = $request->route('tenant');

        // If company is a search_code, find the company
        if (is_string($company)) {
            $company = Company::query()->where('search_code', $company)->first();
        }

        // If we have a company, verify access
        if ($company instanceof Company) {
            // Elevated users can access any company
            if ($user->hasAnyRole(\Modules\Core\Enums\UserRole::elevated())) {
                return $this->setCurrentCompany($company, $request, $next);
            }

            // Regular users must be associated with the company
            if ( ! $user->companies->contains('id', $company->id)) {
                abort(403, 'You do not have access to this company.');
            }

            return $this->setCurrentCompany($company, $request, $next);
        }

        // If no company specified, check if user has access to any company
        if ($user->companies->isEmpty() && ! $user->hasAnyRole(\Modules\Core\Enums\UserRole::elevated())) {
            abort(403, 'You do not have access to any companies.');
        }

        // Use the company from session or user's first company
        $company = session('current_company_id')
            ? Company::query()->find(session('current_company_id'))
            : $user->companies->first();

        if ($company) {
            return $this->setCurrentCompany($company, $request, $next);
        }

        return $next($request);
    }

    /**
     * Set the current company in the session and update route parameter.
     */
    protected function setCurrentCompany(Company $company, Request $request, Closure $next): Response
    {
        session(['current_company_id' => $company->id]);

        // Update route parameter if it exists
        if ($request->route() && $request->route()->hasParameter('tenant')) {
            $request->route()->setParameter('tenant', Str::lower($company->search_code));
        }

        return $next($request);
    }
}
