<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;
use Symfony\Component\HttpFoundation\Response;

class FailedPasswordResetLinkRequestResponse implements FailedPasswordResetLinkRequestResponseContract
{
    /**
     * Create a new response instance.
     */
    public function __construct(
        protected string $status
    ) {}

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     *
     * @throws ValidationException
     */
    public function toResponse($request): Response
    {
        $message = trans($this->status);

        if ($this->status === Password::RESET_THROTTLED) {
            $sentAt = $request->session()->get('password_reset_sent_at');
            $elapsed = $sentAt ? (now()->timestamp - $sentAt) : 0;
            $remaining = max(1, 60 - $elapsed);
            $message = __('passwords.throttled_with_seconds', ['seconds' => $remaining]);
        }

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $message]);
    }
}
