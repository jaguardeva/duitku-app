<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): Response
    {
        $query = User::query()->where('role', 'user');

        if ($request->filled('search')) {
            $search = '%'.$request->input('search').'%';
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        if ($request->filled('status') && in_array($request->input('status'), ['active', 'suspended'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('verification')) {
            if ($request->input('verification') === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->input('verification') === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $sortBy = (string) $request->input('sort_by', 'created_at');
        if (! in_array($sortBy, ['name', 'email', 'status', 'email_verified_at', 'created_at'], true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) $request->input('sort_direction', 'desc'));
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $query->orderBy($sortBy, $sortDir);
        $query->orderBy('id', 'desc');

        $users = $query->paginate($perPage)->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => [
                ...$request->only(['search', 'status', 'verification']),
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDir,
            ],
        ]);
    }

    public function toggleSuspension(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        // Safety rule: Admin cannot suspend themselves
        if ($admin->id === $user->id) {
            throw ValidationException::withMessages([
                'user' => [__('messages.admin.cannot_suspend_self')],
            ]);
        }

        $newStatus = $user->isSuspended() ? 'active' : 'suspended';
        $reason = $request->input('reason');

        $user->update([
            'status' => $newStatus,
        ]);

        $action = $newStatus === 'suspended' ? 'user.suspended' : 'user.activated';

        $this->auditLogService->log(
            action: $action,
            targetType: User::class,
            targetId: $user->id,
            metadata: [
                'target_email' => $user->email,
                'target_name' => $user->name,
                'reason' => $reason ? trim((string) $reason) : null,
            ]
        );

        $message = $newStatus === 'suspended'
            ? 'messages.admin.user_suspended'
            : 'messages.admin.user_restored';

        return back()->with('success', __($message, ['name' => $user->name]));
    }
}
