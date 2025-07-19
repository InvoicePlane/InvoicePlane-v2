<?php

namespace Modules\Core\Filament\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as BaseLoginResponse;
use Illuminate\Support\Str;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;

class LoginResponse implements BaseLoginResponse
{
    public function toResponse($request): mixed
    {
        $user          = auth()->user();
        $elevatedRoles = UserRole::elevated();
        $isElevated    = false;

        foreach ($elevatedRoles as $role) {
            if ($user->hasRole($role)) {
                $isElevated = true;
                break;
            }
        }

        if ($isElevated) {
            $tenant = Company::query()->first();
            if ( ! $tenant) {
                abort(500, 'Fallback company not found.');
            }
        } else {
            $tenant = $user->companies()->first();
            if ( ! $tenant) {
                abort(500, 'No company found for this user.');
            }
        }

        session(['current_company_id' => $tenant->id]);
        filament()->setTenant($tenant);

        return redirect()->route('filament.company.pages.dashboard', [
            'tenant' => Str::lower($tenant->search_code),
        ]);
    }
}
