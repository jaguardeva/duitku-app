<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use Illuminate\Support\Carbon;

class AuditLogService
{
    /**
     * Record a sensitive administrator action.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function log(
        string $action,
        ?string $targetType = null,
        ?string $targetId = null,
        ?array $metadata = null
    ): AdminAuditLog {
        $admin = auth()->user();
        $request = request();

        return AdminAuditLog::create([
            'admin_user_id' => $admin?->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 500) : null,
            'created_at' => Carbon::now('UTC'),
        ]);
    }
}
