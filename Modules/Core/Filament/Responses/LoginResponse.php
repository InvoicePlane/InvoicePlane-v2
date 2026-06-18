<?php

namespace Modules\Core\Filament\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as BaseLoginResponse;
use Illuminate\Support\Str;
use Modules\Core\Enums\UserRole;

class LoginResponse implements BaseLoginResponse
{
    public const string DEFAULT_COMPANY_CODE = 'ivplv2';

    public function toResponse($request): mixed
    {
        $user       = auth()->user();
        $isElevated = $user->hasAnyRole(UserRole::elevated());

        if ($isElevated) {
            $tenant = \Modules\Core\Models\Company::query()
                ->whereRaw('LOWER(search_code) = ?', [self::DEFAULT_COMPANY_CODE])
                ->first()
                ?? \Modules\Core\Models\Company::query()->first();

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
