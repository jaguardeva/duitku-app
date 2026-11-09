<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Transaction::class);

        $user = $request->user();

        $query = Transaction::query()
            ->where('user_id', $user->id)
            ->with(['wallet:id,name,currency_code', 'category:id,name,icon,color_token']);

        // Filters
        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->string('start_date')->toString());
        }

        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->string('end_date')->toString());
        }

        if ($request->filled('type') && in_array($request->input('type'), ['income', 'expense'], true)) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('wallet_id')) {
            $query->where('wallet_id', $request->input('wallet_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->input('search').'%';
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', $search)
                    ->orWhere('notes', 'like', $search);
            });
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $wallets = Wallet::query()->where('user_id', $user->id)->active()->get(['id', 'name', 'currency_code']);
        $categories = Category::query()->forUserOrSystem($user->id)->active()->get(['id', 'name', 'type', 'icon', 'color_token']);

        return Inertia::render('app/transactions/Index', [
            'transactions' => $transactions,
            'wallets' => $wallets,
            'categories' => $categories,
            'filters' => $request->only(['start_date', 'end_date', 'type', 'wallet_id', 'category_id', 'search']),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Transaction::class);

        $user = $request->user();
        $wallets = Wallet::query()->where('user_id', $user->id)->active()->get(['id', 'name', 'currency_code', 'type']);
        $categories = Category::query()->forUserOrSystem($user->id)->active()->get(['id', 'name', 'type', 'icon', 'color_token']);

        return Inertia::render('app/transactions/Form', [
            'wallets' => $wallets,
            'categories' => $categories,
            'defaultType' => $request->input('type', 'expense'),
            'defaultWalletId' => $request->input('wallet_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Transaction::class);

        $user = $request->user();

        $validated = $request->validate([
            'wallet_id' => ['required', 'uuid'],
            'category_id' => ['required', 'uuid'],
            'type' => ['required', 'string', Rule::in(['income', 'expense'])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'title' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date_format:Y-m-d'],
            'transaction_time' => ['nullable', 'date_format:H:i'],
        ]);

        // Validate wallet ownership and active state
        $wallet = Wallet::query()
            ->where('id', $validated['wallet_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $wallet || $wallet->is_archived) {
            throw ValidationException::withMessages([
                'wallet_id' => [__('messages.transaction.wallet_invalid')],
            ]);
        }

        // Validate category ownership and type compatibility
        $category = Category::query()
            ->where('id', $validated['category_id'])
            ->forUserOrSystem($user->id)
            ->first();

        if (! $category || $category->is_archived || $category->type !== $validated['type']) {
            throw ValidationException::withMessages([
                'category_id' => [__('messages.transaction.category_invalid')],
            ]);
        }

        DB::transaction(function () use ($user, $wallet, $category, $validated): void {
            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'category_id' => $category->id,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'currency_code' => $wallet->currency_code,
                'title' => trim($validated['title']),
                'notes' => isset($validated['notes']) ? trim($validated['notes']) : null,
                'transaction_date' => $validated['transaction_date'],
                'transaction_time' => $validated['transaction_time'] ?? null,
            ]);
        });

        $message = $validated['type'] === 'income'
            ? 'messages.transaction.income_created'
            : 'messages.transaction.expense_created';

        return redirect()->route('transactions.index')->with('success', __($message));
    }

    public function edit(Transaction $transaction): Response
    {
        Gate::authorize('update', $transaction);

        $user = auth()->user();
        $wallets = Wallet::query()->where('user_id', $user->id)->get(['id', 'name', 'currency_code', 'is_archived']);
        $categories = Category::query()->forUserOrSystem($user->id)->get(['id', 'name', 'type', 'icon', 'color_token']);

        return Inertia::render('app/transactions/Form', [
            'transaction' => $transaction,
            'wallets' => $wallets,
            'categories' => $categories,
            'defaultType' => $transaction->type,
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);

        $user = $request->user();

        $validated = $request->validate([
            'wallet_id' => ['required', 'uuid'],
            'category_id' => ['required', 'uuid'],
            'type' => ['required', 'string', Rule::in(['income', 'expense'])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'title' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date_format:Y-m-d'],
            'transaction_time' => ['nullable', 'date_format:H:i'],
        ]);

        $wallet = Wallet::query()
            ->where('id', $validated['wallet_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $wallet) {
            throw ValidationException::withMessages([
                'wallet_id' => [__('messages.transaction.wallet_missing')],
            ]);
        }

        $category = Category::query()
            ->where('id', $validated['category_id'])
            ->forUserOrSystem($user->id)
            ->first();

        if (! $category || $category->type !== $validated['type']) {
            throw ValidationException::withMessages([
                'category_id' => [__('messages.transaction.category_mismatch')],
            ]);
        }

        DB::transaction(function () use ($transaction, $wallet, $category, $validated): void {
            $transaction->update([
                'wallet_id' => $wallet->id,
                'category_id' => $category->id,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'currency_code' => $wallet->currency_code,
                'title' => trim($validated['title']),
                'notes' => isset($validated['notes']) ? trim($validated['notes']) : null,
                'transaction_date' => $validated['transaction_date'],
                'transaction_time' => $validated['transaction_time'] ?? null,
            ]);
        });

        return redirect()->route('transactions.index')->with('success', __('messages.transaction.updated'));
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', __('messages.transaction.deleted'));
    }
}
