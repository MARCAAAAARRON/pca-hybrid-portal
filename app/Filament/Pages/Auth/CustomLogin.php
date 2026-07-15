<?php

namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (\Filament\Exceptions\TooManyRequestsException $exception) {
            $this->addError('email', __('filament-panels::pages/auth/login.messages.throttled', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => ceil($exception->secondsUntilAvailable / 60),
            ]));
            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if ($user instanceof FilamentUser) {
            // First check if they have a valid role at all
            if (!in_array($user->role, array_keys(\App\Models\User::ROLE_CHOICES))) {
                Filament::auth()->logout();
                $this->throwFailureValidationException();
            }

            // Then check if they are approved
            if (!$user->is_approved) {
                // If they are not approved, we just let them log in.
                // The Panel's middleware (Authenticate) will call canAccessPanel, throw 403, 
                // and our custom Exception Handler in bootstrap/app.php will redirect them 
                // to the pending approval page gracefully.
            }
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
