<?php

namespace Modules\Core\Filament\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as BaseLoginResponse;

class LoginResponse implements BaseLoginResponse
{
    public function toResponse($request): mixed
    {
        $user = auth()->user();

        // If session is missing, fallback to first company
        if ( ! session()?->has('current_company_id')) {
            $company = $user?->companies()->first();
            if ($company) {
                session(['current_company_id' => $company->id]);
            }
        }

        return redirect()->intended(filament()->getPanel('company')?->getUrl());
    }
}
