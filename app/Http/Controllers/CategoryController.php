<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Category::class);

        $categories = Category::query()
            ->forUserOrSystem($request->user()->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('app/categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Category::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'string', Rule::in(['income', 'expense'])],
            'icon' => ['nullable', 'string', 'max:60'],
            'color_token' => ['nullable', 'string', 'max:30'],
        ]);

        $category = Category::create([
            'user_id' => $request->user()->id,
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? 'circle-ellipsis',
            'color_token' => $validated['color_token'] ?? 'slate',
            'is_system' => false,
            'is_archived' => false,
            'sort_order' => 0,
        ]);

        return redirect()->route('categories.index')->with('success', __('messages.category.created', ['name' => $category->name]));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'icon' => ['nullable', 'string', 'max:60'],
            'color_token' => ['nullable', 'string', 'max:30'],
        ]);

        $category->update([
            'name' => trim($validated['name']),
            'icon' => $validated['icon'] ?? $category->icon,
            'color_token' => $validated['color_token'] ?? $category->color_token,
        ]);

        return redirect()->route('categories.index')->with('success', __('messages.category.updated', ['name' => $category->name]));
    }

    public function toggleArchive(Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $category->update([
            'is_archived' => ! $category->is_archived,
        ]);

        $message = $category->is_archived ? 'messages.category.archived' : 'messages.category.restored';

        return redirect()->route('categories.index')->with('success', __($message, ['name' => $category->name]));
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        if ($category->transactions()->exists() || $category->budgets()->exists()) {
            throw ValidationException::withMessages([
                'category' => [__('messages.category.cannot_delete')],
            ]);
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('categories.index')->with('success', __('messages.category.deleted', ['name' => $name]));
    }
}
