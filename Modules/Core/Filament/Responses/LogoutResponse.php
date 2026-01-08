<?php

namespace Modules\Core\Filament\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as BaseLogoutResponse;

class LogoutResponse implements BaseLogoutResponse
{
    public function toResponse($request)
    {
        // Redirect to the root login page after logout
        return redirect()->route('filament.company.auth.login');
    }
}
