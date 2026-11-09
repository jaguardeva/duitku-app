<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserSessionService
{
    /**
     * @return Collection<int, array{
     *     id: string,
     *     ip_address: string,
     *     is_current_device: bool,
     *     agent: array{
     *         device_type: 'desktop'|'mobile'|'tablet'|'bot',
     *         platform: string,
     *         browser: string,
     *         display_name: string
     *     },
     *     last_active: string,
     *     last_active_at: string
     * }>
     */
    public function forUser(User $user, string $currentSessionId): Collection
    {
        $appKey = (string) config('app.key');
        $sessionTable = (string) config('session.table', 'sessions');

        return DB::table($sessionTable)
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($currentSessionId, $appKey): array {
                $isCurrent = $session->id === $currentSessionId;
                $agent = UserAgentParser::parse($session->user_agent);
                $lastActiveTime = Carbon::createFromTimestamp($session->last_activity);

                return [
                    'id' => hash('sha256', $session->id.$appKey),
                    'ip_address' => $session->ip_address ?? '127.0.0.1',
                    'is_current_device' => $isCurrent,
                    'agent' => $agent,
                    'last_active' => $isCurrent ? __('frontend.common.active_now') : $lastActiveTime->locale(app()->getLocale())->diffForHumans(),
                    'last_active_at' => $lastActiveTime->toIso8601String(),
                ];
            })
            ->values();
    }
}
