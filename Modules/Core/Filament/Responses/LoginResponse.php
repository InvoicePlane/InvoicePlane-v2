<?php

namespace Modules\Core\Filament\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as BaseLoginResponse;
use Illuminate\Support\Str;
use Modules\Core\Models\Company;

class LoginResponse implements BaseLoginResponse
{
    public function toResponse($request): mixed
    {
        $tenant = Company::query()->find(1);

        if ( ! $tenant) {
            abort(500, 'Fallback company not found.');
        }

        filament()->setTenant($tenant);
        session(['current_company_id' => $tenant->id]);

        return redirect()->route('filament.company.pages.dashboard', [
            'tenant' => Str::lower($tenant->getRouteKey()),
        ]);
    }
}
