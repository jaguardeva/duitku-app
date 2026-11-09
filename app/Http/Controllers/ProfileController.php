<?php

namespace App\Http\Controllers;

use App\Services\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private UserSessionService $userSessionService
    ) {}

    /**
     * Display user information settings page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('app/profile/Index', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'timezone' => $user->timezone,
                'locale' => $user->locale,
                'default_currency' => $user->default_currency,
                'email_verified' => $user->isEmailVerified(),
            ],
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    /**
     * Update user information profile settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'locale' => ['required', 'string', Rule::in(['id', 'en'])],
            'default_currency' => ['nullable', 'string', 'in:IDR,idr'],
        ]);

        $user->update([
            'name' => trim($validated['name']),
            'timezone' => $validated['timezone'],
            'locale' => $validated['locale'],
            'default_currency' => 'IDR',
        ]);

        $request->session()->put('locale', $validated['locale']);
        cookie()->queue('catatduit_locale', $validated['locale'], 60 * 24 * 365);
        App::setLocale($validated['locale']);

        return back()->with('success', __('messages.profile.updated'));
    }

    /**
     * Display security and password management page.
     */
    public function security(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('app/profile/Security', [
            'email' => $user->email,
            'email_verified' => $user->isEmailVerified(),
            'sessions' => $this->userSessionService->forUser($user, (string) $request->session()->getId()),
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
                'regex:/^\S*$/',
                'confirmed',
            ],
        ], [
            'password.regex' => __('validation.custom.password.no_spaces'),
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', __('messages.profile.password_updated'));
    }
}
