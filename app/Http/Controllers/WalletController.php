<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Services\BalanceCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    public function __construct(
        protected BalanceCalculationService $balanceService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Wallet::class);

        $wallets = $this->balanceService->getWalletsWithCalculatedBalance($request->user(), includeArchived: true);
        $balancesByCurrency = $this->balanceService->getBalancesGroupedByCurrency($request->user());

        return Inertia::render('app/wallets/Index', [
            'wallets' => $wallets,
            'balancesByCurrency' => $balancesByCurrency,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Wallet::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'string', Rule::in(['cash', 'bank', 'e_wallet', 'savings', 'other'])],
            'currency_code' => ['nullable', 'string', 'in:IDR,idr'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'icon' => ['nullable', 'string', 'max:60'],
            'color_token' => ['nullable', 'string', 'max:30'],
        ]);

        $wallet = Wallet::create([
            'user_id' => $request->user()->id,
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'currency_code' => 'IDR',
            'opening_balance' => $validated['opening_balance'],
            'icon' => $validated['icon'] ?? 'wallet',
            'color_token' => $validated['color_token'] ?? 'blue',
            'is_archived' => false,
            'sort_order' => 0,
        ]);

        return redirect()->route('wallets.index')->with('success', __('messages.wallet.created', ['name' => $wallet->name]));
    }

    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        Gate::authorize('update', $wallet);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'string', Rule::in(['cash', 'bank', 'e_wallet', 'savings', 'other'])],
            'icon' => ['nullable', 'string', 'max:60'],
            'color_token' => ['nullable', 'string', 'max:30'],
        ]);

        $wallet->update([
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? $wallet->icon,
            'color_token' => $validated['color_token'] ?? $wallet->color_token,
        ]);

        return redirect()->route('wallets.index')->with('success', __('messages.wallet.updated', ['name' => $wallet->name]));
    }

    public function toggleArchive(Wallet $wallet): RedirectResponse
    {
        Gate::authorize('update', $wallet);

        $wallet->update([
            'is_archived' => ! $wallet->is_archived,
        ]);

        $message = $wallet->is_archived ? 'messages.wallet.archived' : 'messages.wallet.restored';

        return redirect()->route('wallets.index')->with('success', __($message, ['name' => $wallet->name]));
    }

    public function destroy(Wallet $wallet): RedirectResponse
    {
        Gate::authorize('delete', $wallet);

        // Disallow deletion if transactions or transfers exist
        if ($wallet->transactions()->exists() || $wallet->transfersAsSource()->exists() || $wallet->transfersAsDestination()->exists()) {
            throw ValidationException::withMessages([
                'wallet' => [__('messages.wallet.cannot_delete')],
            ]);
        }

        $walletName = $wallet->name;
        $wallet->delete();

        return redirect()->route('wallets.index')->with('success', __('messages.wallet.deleted', ['name' => $walletName]));
    }
}
