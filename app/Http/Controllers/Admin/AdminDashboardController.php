<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $thirtyDaysAgo = Carbon::now('UTC')->subDays(30);

        $metrics = [
            'total_users' => User::query()->where('role', 'user')->count(),
            'active_users' => User::query()->where('role', 'user')->where('status', 'active')->count(),
            'suspended_users' => User::query()->where('role', 'user')->where('status', 'suspended')->count(),
            'verified_users' => User::query()->where('role', 'user')->whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::query()->where('role', 'user')->whereNull('email_verified_at')->count(),
            'new_registrations_30d' => User::query()->where('role', 'user')->where('created_at', '>=', $thirtyDaysAgo)->count(),
            'system_categories' => Category::query()->where('is_system', true)->count(),
            'income_categories' => Category::query()->where('is_system', true)->where('type', 'income')->count(),
            'expense_categories' => Category::query()->where('is_system', true)->where('type', 'expense')->count(),
            'total_audit_logs' => AdminAuditLog::query()->count(),
        ];

        $recentAuditLogs = AdminAuditLog::query()
            ->with('admin:id,name,email')
            ->latest('created_at')
            ->take(5)
            ->get();

        return Inertia::render('admin/Dashboard', [
            'metrics' => $metrics,
            'recentAuditLogs' => $recentAuditLogs,
        ]);
    }

    public function updateVersion(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $validated = $request->validate([
            'version' => ['required', 'string', 'max:30', 'regex:/^v?[0-9]+(\.[0-9]+)*(-[a-zA-Z0-9.]+)?$/'],
        ]);

        $oldVersion = (string) AppSetting::get('app_version', config('app.version', '1.0.0'));
        $newVersion = (string) $validated['version'];

        AppSetting::set('app_version', $newVersion);

        $auditLog->log(
            action: 'system.version_update',
            targetType: 'system',
            targetId: null,
            metadata: [
                'old_version' => $oldVersion,
                'new_version' => $newVersion,
            ]
        );

        return back()->with('success', __('messages.admin.version_updated'));
    }
}
