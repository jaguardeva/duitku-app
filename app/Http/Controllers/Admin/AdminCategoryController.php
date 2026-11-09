<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminCategoryController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): Response
    {
        $query = Category::query()
            ->where('is_system', true);

        if ($request->filled('search')) {
            $search = '%'.$request->input('search').'%';
            $query->where('name', 'like', $search);
        }

        if ($request->filled('type') && in_array($request->input('type'), ['income', 'expense'], true)) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_archived', false);
            } elseif ($request->input('status') === 'archived') {
                $query->where('is_archived', true);
            }
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $sortBy = (string) $request->input('sort_by', 'sort_order');
        if (! in_array($sortBy, ['name', 'type', 'sort_order', 'is_archived', 'created_at'], true)) {
            $sortBy = 'sort_order';
        }

        $sortDir = strtolower((string) $request->input('sort_direction', 'asc'));
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'asc';
        }

        $query->orderBy($sortBy, $sortDir);
        if ($sortBy !== 'name') {
            $query->orderBy('name', 'asc');
        }

        $categories = $query->paginate($perPage)->withQueryString();

        return Inertia::render('admin/categories/Index', [
            'categories' => $categories,
            'filters' => [
                ...$request->only(['search', 'type', 'status']),
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDir,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'string', Rule::in(['income', 'expense'])],
            'icon' => ['nullable', 'string', 'max:60'],
            'color_token' => ['nullable', 'string', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = Category::create([
            'user_id' => null,
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? 'circle-ellipsis',
            'color_token' => $validated['color_token'] ?? 'blue',
            'is_system' => true,
            'is_archived' => false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $this->auditLogService->log(
            action: 'category.created',
            targetType: Category::class,
            targetId: $category->id,
            metadata: ['name' => $category->name, 'type' => $category->type]
        );

        return back()->with('success', __('messages.admin.system_category_created', ['name' => $category->name]));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'icon' => ['nullable', 'string', 'max:60'],
            'color_token' => ['nullable', 'string', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category->update([
            'name' => trim($validated['name']),
            'icon' => $validated['icon'] ?? $category->icon,
            'color_token' => $validated['color_token'] ?? $category->color_token,
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
        ]);

        $this->auditLogService->log(
            action: 'category.updated',
            targetType: Category::class,
            targetId: $category->id,
            metadata: ['name' => $category->name]
        );

        return back()->with('success', __('messages.admin.system_category_updated', ['name' => $category->name]));
    }

    public function toggleArchive(Category $category): RedirectResponse
    {
        $category->update([
            'is_archived' => ! $category->is_archived,
        ]);

        $message = $category->is_archived ? 'messages.category.archived' : 'messages.category.restored';

        $this->auditLogService->log(
            action: $category->is_archived ? 'category.archived' : 'category.unarchived',
            targetType: Category::class,
            targetId: $category->id,
            metadata: ['name' => $category->name]
        );

        return back()->with('success', __($message, ['name' => $category->name]));
    }
}
