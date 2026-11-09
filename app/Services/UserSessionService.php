<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class UserSessionService
{
    /**
     * @return Collection<int, array{
     *     id: string,
     *     ip_address: string,
     *     is_current_device: bool,
     *     agent: array{
     *         device_type: string,
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
            ->map(function (stdClass $session) use ($currentSessionId, $appKey): array {
                $isCurrent = $session->id === $currentSessionId;
                $agent = UserAgentParser::parse($session->user_agent !== null ? (string) $session->user_agent : null);
                $lastActiveTime = new CarbonImmutable('@'.(string) $session->last_activity);
                $lastActiveTime->setLocale(app()->getLocale());
                $lastActive = $isCurrent
                    ? (string) __('frontend.common.active_now')
                    : (string) $lastActiveTime->diffForHumans();

                return [
                    'id' => hash('sha256', $session->id.$appKey),
                    'ip_address' => (string) ($session->ip_address ?? '127.0.0.1'),
                    'is_current_device' => $isCurrent,
                    'agent' => $agent,
                    'last_active' => $lastActive,
                    'last_active_at' => $lastActiveTime->toIso8601String(),
                ];
            })
            ->values();
    }
}
