<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserSessionController extends Controller
{
    /**
     * Display a list of active login sessions for the authenticated user.
     */
    public function index(): RedirectResponse
    {
        return to_route('profile.security');
    }

    /**
     * Revoke a specific active session.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $user = $request->user();
        $currentSessionId = (string) $request->session()->getId();
        $appKey = (string) config('app.key');
        $sessionTable = (string) config('session.table', 'sessions');

        $sessions = DB::table($sessionTable)
            ->where('user_id', $user->id)
            ->get();

        $target = $sessions->first(function ($session) use ($id, $appKey) {
            return hash('sha256', $session->id.$appKey) === $id;
        });

        if (! $target) {
            return back()->with('error', __('messages.sessions.not_found'));
        }

        if ($target->id === $currentSessionId) {
            return back()->with('error', __('messages.sessions.current'));
        }

        DB::table($sessionTable)
            ->where('id', $target->id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', __('messages.sessions.revoked'));
    }

    /**
     * Revoke all other active sessions except the current one.
     */
    public function destroyOthers(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Kata sandi diperlukan untuk mengonfirmasi tindakan ini.',
            'password.current_password' => 'Kata sandi yang Anda masukkan salah.',
        ]);

        $user = $request->user();
        $currentSessionId = (string) $request->session()->getId();
        $sessionTable = (string) config('session.table', 'sessions');

        DB::table($sessionTable)
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return back()->with('success', __('messages.sessions.others_revoked'));
    }

    /**
     * Revoke all active sessions including the current one (global logout).
     */
    public function destroyAll(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Kata sandi diperlukan untuk mengonfirmasi tindakan ini.',
            'password.current_password' => 'Kata sandi yang Anda masukkan salah.',
        ]);

        $user = $request->user();
        $sessionTable = (string) config('session.table', 'sessions');

        DB::table($sessionTable)
            ->where('user_id', $user->id)
            ->delete();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', __('messages.sessions.all_revoked'));
    }
}
