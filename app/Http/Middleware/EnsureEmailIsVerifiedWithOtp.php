<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerifiedWithOtp
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isEmailVerified()) {
            return $request->expectsJson()
                ? abort(403, 'Email Anda belum diverifikasi.')
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
