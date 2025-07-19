<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\Company;

class SetTenantFromRouteForSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user     = Auth::user();
        $tenantId = $request->route('tenant');

        dd('here set Tenant for Superadmin?');
        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() && $tenantId) {
            // You may want to resolve by search_code or id. Adjust as needed:
            $company = Company::where('id', $tenantId)
                ->first();

            if ($company) {
                // Set tenant context for Filament
                if (function_exists('filament')) {
                    filament()->setTenant($company);
                }
                // Do NOT set session for super_admins on Filament panel routes
            }
        }

        return $next($request);
    }
}
