<?php

namespace App\Services;

use App\Mail\SendOtpMail;
use App\Models\EmailVerificationOtp;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public const EXPIRY_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Generate and dispatch a new OTP to the user.
     * Invalidates any previously active OTP for this user.
     *
     * @throws ValidationException
     */
    public function sendOtp(User $user): EmailVerificationOtp
    {
        return DB::transaction(function () use ($user): EmailVerificationOtp {
            // Check resend cooldown
            $recentOtp = EmailVerificationOtp::query()
                ->where('user_id', $user->id)
                ->orderByDesc('sent_at')
                ->first();

            if ($recentOtp) {
                $now = Carbon::now('UTC');
                $cooldownUntil = $recentOtp->sent_at->copy()->addSeconds(self::RESEND_COOLDOWN_SECONDS);

                if ($now->isBefore($cooldownUntil)) {
                    $secondsLeft = (int) ceil($now->diffInSeconds($cooldownUntil, true));

                    throw ValidationException::withMessages([
                        'otp' => [__('messages.otp.wait', ['seconds' => $secondsLeft])],
                    ]);
                }
            }

            // Invalidate any unconsumed OTPs
            EmailVerificationOtp::query()
                ->where('user_id', $user->id)
                ->whereNull('consumed_at')
                ->lockForUpdate()
                ->update(['consumed_at' => Carbon::now('UTC')]);

            // Generate 6 digit code cryptographically
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $codeHash = hash('sha256', $code);

            $otp = EmailVerificationOtp::create([
                'user_id' => $user->id,
                'code_hash' => $codeHash,
                'expires_at' => Carbon::now('UTC')->addMinutes(self::EXPIRY_MINUTES),
                'attempts' => 0,
                'max_attempts' => self::MAX_ATTEMPTS,
                'sent_at' => Carbon::now('UTC'),
            ]);

            // Send via mail
            Mail::to($user->email)->send(new SendOtpMail($code, self::EXPIRY_MINUTES));

            return $otp;
        });
    }

    /**
     * Verify the supplied OTP code.
     *
     * @return array{success: bool, message: string}
     */
    public function verifyOtp(User $user, string $code): array
    {
        return DB::transaction(function () use ($user, $code): array {
            $otp = EmailVerificationOtp::query()
                ->where('user_id', $user->id)
                ->whereNull('consumed_at')
                ->orderByDesc('created_at')
                ->lockForUpdate()
                ->first();

            if (! $otp) {
                return [
                    'success' => false,
                    'message' => __('messages.otp.no_active_code'),
                ];
            }

            if ($otp->isExpired()) {
                $otp->update(['consumed_at' => Carbon::now('UTC')]);

                return [
                    'success' => false,
                    'message' => __('messages.otp.expired'),
                ];
            }

            if ($otp->hasExceededAttempts()) {
                $otp->update(['consumed_at' => Carbon::now('UTC')]);

                return [
                    'success' => false,
                    'message' => __('messages.otp.attempts_exhausted'),
                ];
            }

            $inputHash = hash('sha256', trim($code));

            if (hash_equals($otp->code_hash, $inputHash)) {
                $otp->update(['consumed_at' => Carbon::now('UTC')]);
                $user->update(['email_verified_at' => Carbon::now('UTC')]);

                return [
                    'success' => true,
                    'message' => __('messages.otp.verified'),
                ];
            }

            $otp->increment('attempts');
            $remaining = max(0, $otp->max_attempts - $otp->attempts);

            if ($remaining === 0) {
                $otp->update(['consumed_at' => Carbon::now('UTC')]);

                return [
                    'success' => false,
                    'message' => __('messages.otp.incorrect_exhausted'),
                ];
            }

            return [
                'success' => false,
                'message' => __('messages.otp.incorrect_remaining', ['remaining' => $remaining]),
            ];
        });
    }

    /**
     * Get remaining cooldown seconds for resending OTP.
     */
    public function getResendCooldownSeconds(User $user): int
    {
        $recentOtp = EmailVerificationOtp::query()
            ->where('user_id', $user->id)
            ->orderByDesc('sent_at')
            ->first();

        if (! $recentOtp) {
            return 0;
        }

        $cooldownUntil = $recentOtp->sent_at->copy()->addSeconds(self::RESEND_COOLDOWN_SECONDS);

        if (! Carbon::now('UTC')->isBefore($cooldownUntil)) {
            return 0;
        }

        return (int) ceil(Carbon::now('UTC')->diffInSeconds($cooldownUntil, true));
    }

    /**
     * Get the absolute Unix timestamp when OTP resend becomes available.
     */
    public function getResendCooldownUntil(User $user): ?int
    {
        $recentOtp = EmailVerificationOtp::query()
            ->where('user_id', $user->id)
            ->orderByDesc('sent_at')
            ->first();

        if (! $recentOtp) {
            return null;
        }

        $cooldownUntil = $recentOtp->sent_at->copy()->addSeconds(self::RESEND_COOLDOWN_SECONDS);

        return $cooldownUntil->isFuture() ? (int) ceil($cooldownUntil->valueOf() / 1000) : null;
    }
}
