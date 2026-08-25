<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Enums\PanelTheme;
use Modules\Core\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the current company's `panel_theme` setting to the panel for this
 * request.
 *
 * `Panel::viteTheme()` is normally called once in the panel provider, at
 * service-provider boot, long before there is a tenant to read a setting
 * from. It can be re-pointed later because the panel only resolves the
 * stylesheet at render time -- `filament()->getTheme()` is evaluated inside
 * the layout's `<head>` (see `filament::components.layout.base`). So mutating
 * the panel from middleware is enough, provided the middleware runs before
 * the response is rendered.
 *
 * It is registered in the company panel's `tenantMiddleware`, which Filament
 * always prefixes with its own `IdentifyTenant`, so the tenant is resolved by
 * the time this runs.
 *
 * The theme is set unconditionally, including when the company has not chosen
 * one: the panel object is a singleton, so under a persistent worker (Octane)
 * an early return would leave the previous request's company theme in place
 * for the next one.
 */
class ApplyCompanyTheme
{
    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentPanel();

        if ($panel === null) {
            return $next($request);
        }

        $companyId = $this->resolveCompanyId();

        $theme = $companyId === null
            ? PanelTheme::default()
            : PanelTheme::fromValue(Setting::getForCompany($companyId, Setting::KEY_PANEL_THEME));

        $panel->viteTheme($theme->viteEntrypoint());

        return $next($request);
    }

    /**
     * Tenant first, then the session, then the user's first company -- the
     * same order the tenant middleware chain resolves in, so a request that
     * arrives before the tenant is on the route still themes correctly.
     */
    private function resolveCompanyId(): ?int
    {
        $tenant = Filament::getTenant();

        if ($tenant !== null) {
            return (int) $tenant->getKey();
        }

        $sessionId = session('current_company_id');

        if ($sessionId !== null) {
            return (int) $sessionId;
        }

        $company = Auth::user()?->companies()->first();

        return $company === null ? null : (int) $company->getKey();
    }
}
