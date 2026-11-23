<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //'email' => ['required', 'string', 'email'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => trans('auth.errors.empty_login'),
            'password.required' => trans('auth.errors.empty_password'),
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            'samaccountname' => $this->username,
            'password' => $this->password,
        ];

        //dd($credentials);

        try {
            $authenticated = Auth::attempt($credentials, $this->filled('remember'));
        } catch (\Illuminate\Database\QueryException $e) {
            // У учётки в AD нет почты, а в users.email она обязательна — раньше пользователь получал голый 500
            if (! str_contains($e->getMessage(), "'email'")) {
                throw $e;
            }
            \Illuminate\Support\Facades\Log::warning("Login of {$this->username} failed: no e-mail in AD");

            throw ValidationException::withMessages([
                'samaccountname' => trans('auth.errors.no_email'),
            ]);
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'samaccountname' => trans('auth.errors.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'samaccountname' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('samaccountname')).'|'.$this->ip());
    }
}
