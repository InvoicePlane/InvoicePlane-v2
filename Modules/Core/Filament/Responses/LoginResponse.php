<?php

namespace Modules\Core\Filament\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as BaseLoginResponse;
use Illuminate\Support\Str;

class LoginResponse implements BaseLoginResponse
{
    public const DEFAULT_COMPANY_CODE = 'ivplv2';

    public function toResponse($request): mixed
    {
        $user = auth()->user();

        $tenant = $user->companies()
            ->whereRaw('LOWER(search_code) = ?', [self::DEFAULT_COMPANY_CODE])
            ->first()
            ?? $user->companies()->first();

        if ( ! $tenant) {
            abort(403, 'No company found for your account. Please contact an administrator.');
        }

        session(['current_company_id' => $tenant->id]);
        filament()->setTenant($tenant);

        return redirect()->route('filament.company.pages.dashboard', [
            'tenant' => Str::lower($tenant->search_code),
        ]);
    }
}
