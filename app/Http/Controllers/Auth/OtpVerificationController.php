<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OtpVerificationController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Display the OTP verification notice / form.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->isEmailVerified()) {
            return redirect()->intended('/app');
        }

        return Inertia::render('auth/VerifyOtp', [
            'email' => $user?->email,
            'cooldownUntil' => $user ? $this->otpService->getResendCooldownUntil($user) : null,
        ]);
    }

    /**
     * Process verification of the submitted OTP code.
     *
     * @throws ValidationException
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => __('validation.required', ['attribute' => __('validation.attributes.code')]),
            'code.size' => __('validation.size.string', ['attribute' => __('validation.attributes.code'), 'size' => 6]),
        ]);

        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $result = $this->otpService->verifyOtp($user, (string) $request->input('code'));

        if (! $result['success']) {
            throw ValidationException::withMessages([
                'code' => [$result['message']],
            ]);
        }

        return redirect()->intended('/app')->with('success', $result['message']);
    }

    /**
     * Resend a fresh OTP to the user.
     *
     * @throws ValidationException
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isEmailVerified()) {
            return redirect()->route('app.dashboard');
        }

        $this->otpService->sendOtp($user);

        return back()->with('success', __('messages.otp.resent'));
    }
}
