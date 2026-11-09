<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        // Normalize email
        $input['email'] = strtolower(trim((string) ($input['email'] ?? '')));

        Validator::make($input, [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:254',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'timezone' => ['nullable', 'string', 'timezone'],
        ], [
            'password.regex' => __('validation.custom.password.no_spaces'),
        ])->validate();

        $timezone = (string) ($input['timezone'] ?? 'UTC');
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = 'UTC';
        }

        $locale = session('locale') ?? request()->cookie('catatduit_locale') ?? app()->getLocale();
        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        $user = User::create([
            'name' => trim((string) $input['name']),
            'email' => $input['email'],
            'password' => Hash::make((string) $input['password']),
            'role' => 'user',
            'status' => 'active',
            'timezone' => $timezone,
            'locale' => $locale,
            'default_currency' => 'IDR',
            'theme_preference' => 'light',
            'email_verified_at' => null,
        ]);

        // Send OTP verification code
        $this->otpService->sendOtp($user);

        return $user;
    }
}
