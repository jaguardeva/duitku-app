<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Custom Fortify responses for persistent cooldown tracking
        $this->app->singleton(
            SuccessfulPasswordResetLinkRequestResponse::class,
            \App\Http\Responses\SuccessfulPasswordResetLinkRequestResponse::class
        );
        $this->app->singleton(
            FailedPasswordResetLinkRequestResponse::class,
            \App\Http\Responses\FailedPasswordResetLinkRequestResponse::class
        );

        // Inertia views for authentication
        Fortify::loginView(fn () => Inertia::render('auth/Login'));
        Fortify::registerView(fn () => Inertia::render('auth/Register'));
        Fortify::requestPasswordResetLinkView(function (Request $request) {
            $sentAt = $request->session()->get('password_reset_sent_at');
            $elapsed = $sentAt ? (now()->timestamp - $sentAt) : 999;
            $cooldownSeconds = max(0, 60 - $elapsed);
            $submittedEmail = $cooldownSeconds > 0 ? (string) $request->session()->get('password_reset_email') : null;

            return Inertia::render('auth/ForgotPassword', [
                'status' => $request->session()->get('status'),
                'cooldownSeconds' => $cooldownSeconds,
                'submittedEmail' => $submittedEmail,
            ]);
        });
        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/ResetPassword', [
            'email' => $request->string('email')->toString(),
            'token' => $request->route('token'),
        ]));

        // Custom authentication logic to prevent suspended users from logging in
        Fortify::authenticateUsing(function (Request $request): ?User {
            $email = Str::lower(trim((string) $request->input(Fortify::username())));
            $password = (string) $request->input('password');

            $user = User::query()->where('email', $email)->first();

            if ($user && Hash::check($password, $user->password)) {
                if ($user->isSuspended()) {
                    throw ValidationException::withMessages([
                        Fortify::username() => [__('messages.auth.suspended')],
                    ]);
                }

                return $user;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
