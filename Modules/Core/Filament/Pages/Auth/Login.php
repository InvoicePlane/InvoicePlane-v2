<?php

namespace Modules\Core\Filament\Pages\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Http\Responses\Auth\LoginResponse as FilamentLoginResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Filament\Resources\UserResource;
use Filament\Pages\Auth\Login as BaseLogin;
use Modules\Core\Models\User;

class Login extends BaseLogin
{
    public string $user_email = '';

    public string $user_password;

    protected static string $resource = UserResource::class;

    protected static string $view = 'core::auth.login';

    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        $this->validate([
            'user_email' => 'required|email',
            'user_password' => 'required',
        ]);

        $user = User::where('user_email', $this->user_email)->first();

        if ( ! $user || ! Hash::check($this->user_password, $user->user_password)) {
            dd('Password verification failed');
        }

        $user = User::where('user_email', $this->user_email)->first();

        Auth::login($user);

        return app(LoginResponse::class);
    }
}
