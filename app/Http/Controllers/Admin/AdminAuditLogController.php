<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AdminAuditLog::query()
            ->with('admin:id,name,email');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->input('action').'%');
        }

        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $sortBy = (string) $request->input('sort_by', 'created_at');
        if (! in_array($sortBy, ['created_at', 'action', 'ip_address'], true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) $request->input('sort_direction', 'desc'));
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $query->orderBy($sortBy, $sortDir);
        if ($sortBy !== 'id') {
            $query->orderBy('id', 'desc');
        }

        $auditLogs = $query->paginate($perPage)->withQueryString();

        return Inertia::render('admin/audit-logs/Index', [
            'auditLogs' => $auditLogs,
            'filters' => [
                ...$request->only(['action']),
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDir,
            ],
        ]);
    }
}
