<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Budget::class);

        $user = $request->user();
        $now = Carbon::now($user->timezone ?: 'UTC');
        $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

        $budgets = Budget::query()
            ->where('user_id', $user->id)
            ->with('category:id,name,icon,color_token')
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Budget $b): Budget {
                $query = Transaction::query()
                    ->where('user_id', $b->user_id)
                    ->where('type', 'expense')
                    ->where('currency_code', $b->currency_code)
                    ->whereBetween('transaction_date', [$b->period_start, $b->period_end]);

                if ($b->category_id) {
                    $query->where('category_id', $b->category_id);
                }

                $spent = (string) $query->sum('amount');
                $b->setAttribute('spent_amount', $spent);

                $percent = $b->amount > 0 ? (float) bcdiv(bcmul($spent, '100', 4), (string) $b->amount, 2) : 0;
                $b->setAttribute('usage_percent', min(100, $percent));
                $b->setAttribute('actual_percent', $percent);
                $b->setAttribute('is_warning', $percent >= $b->warning_threshold_percent);
                $b->setAttribute('is_exceeded', $percent > 100);

                return $b;
            });

        $expenseCategories = Category::query()
            ->forUserOrSystem($user->id)
            ->where('type', 'expense')
            ->active()
            ->get(['id', 'name', 'icon', 'color_token']);

        return Inertia::render('app/budgets/Index', [
            'budgets' => $budgets,
            'expenseCategories' => $expenseCategories,
            'defaultCurrency' => $user->default_currency ?: 'IDR',
            'currentPeriod' => [
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Budget::class);

        $user = $request->user();

        $validated = $request->validate([
            'category_id' => ['nullable', 'uuid'],
            'name' => ['nullable', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency_code' => ['nullable', 'string', 'in:IDR,idr'],
            'period_start' => ['required', 'date_format:Y-m-d'],
            'period_end' => ['required', 'date_format:Y-m-d', 'after_or_equal:period_start'],
            'warning_threshold_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if (! empty($validated['category_id'])) {
            $cat = Category::query()
                ->where('id', $validated['category_id'])
                ->forUserOrSystem($user->id)
                ->where('type', 'expense')
                ->first();

            if (! $cat) {
                throw ValidationException::withMessages([
                    'category_id' => [__('messages.category.invalid_budget')],
                ]);
            }
        }

        $budget = Budget::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'] ?? null,
            'name' => isset($validated['name']) ? trim($validated['name']) : null,
            'amount' => $validated['amount'],
            'currency_code' => 'IDR',
            'period_type' => 'monthly',
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'warning_threshold_percent' => $validated['warning_threshold_percent'] ?? 80,
            'is_active' => true,
        ]);

        return redirect()->route('budgets.index')->with('success', __('messages.budget.created'));
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        Gate::authorize('update', $budget);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'warning_threshold_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $budget->update([
            'amount' => $validated['amount'],
            'warning_threshold_percent' => $validated['warning_threshold_percent'] ?? $budget->warning_threshold_percent,
            'is_active' => $validated['is_active'] ?? $budget->is_active,
        ]);

        return redirect()->route('budgets.index')->with('success', __('messages.budget.updated'));
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        Gate::authorize('delete', $budget);

        $budget->delete();

        return redirect()->route('budgets.index')->with('success', __('messages.budget.deleted'));
    }
}
