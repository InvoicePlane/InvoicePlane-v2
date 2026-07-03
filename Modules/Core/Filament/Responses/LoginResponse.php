<?php

namespace Modules\Core\Filament\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as BaseLoginResponse;
use Illuminate\Support\Str;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;

class LoginResponse implements BaseLoginResponse
{
    private const DEFAULT_COMPANY_CODE = 'ivplv2';

    public function toResponse($request): mixed
    {
        $user       = auth()->user();
        $isElevated = collect(UserRole::elevated())
            ->contains(fn ($role) => $user->hasRole($role));

        if ($isElevated) {
            $tenant = Company::query()
                ->whereRaw('LOWER(search_code) = ?', [self::DEFAULT_COMPANY_CODE])
                ->first()
                ?? Company::query()->oldest('id')->first();

            if (! $tenant) {
                abort(500, trans('auth.fallback_company_not_found'));
            }

            filament()->setTenant($tenant);
        } else {
            $tenant = $user->companies()
                ->whereRaw('LOWER(search_code) = ?', [self::DEFAULT_COMPANY_CODE])
                ->first()
                ?? $user->companies()->oldest('id')->first();

            if (! $tenant) {
                abort(500, trans('auth.no_company_found_for_user'));
            }

            session(['current_company_id' => $tenant->id]);
            filament()->setTenant($tenant);
        }

        session(['current_company_id' => $tenant->id]);
        filament()->setTenant($tenant);

        return redirect()->route('filament.company.pages.dashboard', [
            'tenant' => Str::lower($tenant->search_code),
        ]);
    }
}
