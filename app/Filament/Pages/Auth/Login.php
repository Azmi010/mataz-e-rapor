<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as FilamentLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Login extends FilamentLogin
{
    /**
     * Get the redirect URL after successful authentication.
     * This method is called by Filament after login.
     */
    protected function getRedirectUrl(): string
    {
        $user = Auth::user();

        if (!$user) {
            Log::warning('Login: No authenticated user found');
            return '/admin';
        }

        Log::info('Login: Processing redirect for user', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role
        ]);

        $redirectUrl = match ($user->role) {
            'admin' => '/admin',
            'teacher' => '/teacher',
            'student' => '/student',
            default => '/admin'
        };

        Log::info('Login: Redirecting user', [
            'user_id' => $user->id,
            'role' => $user->role,
            'redirect_url' => $redirectUrl
        ]);

        return $redirectUrl;
    }
}
