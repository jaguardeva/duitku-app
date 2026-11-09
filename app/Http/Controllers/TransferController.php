<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\Wallet;
use App\Services\BalanceCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TransferController extends Controller
{
    public function __construct(
        protected BalanceCalculationService $balanceCalculationService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Transfer::class);

        $user = $request->user();

        $transfers = Transfer::query()
            ->where('user_id', $user->id)
            ->with(['sourceWallet:id,name,currency_code', 'destinationWallet:id,name,currency_code'])
            ->orderByDesc('transfer_date')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $wallets = $this->balanceCalculationService
            ->getWalletsWithCalculatedBalance($user)
            ->map(fn (Wallet $wallet): array => [
                'id' => $wallet->id,
                'name' => $wallet->name,
                'currency_code' => $wallet->currency_code,
                'type' => $wallet->type,
                'current_balance' => $wallet->getAttribute('current_balance'),
            ])
            ->values();

        return Inertia::render('app/transfers/Index', [
            'transfers' => $transfers,
            'wallets' => $wallets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Transfer::class);

        $user = $request->user();

        $validated = $request->validate([
            'source_wallet_id' => ['required', 'uuid', 'different:destination_wallet_id'],
            'destination_wallet_id' => ['required', 'uuid'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'transfer_date' => ['required', 'date_format:Y-m-d'],
            'transfer_time' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'source_wallet_id.different' => 'Dompet asal dan dompet tujuan tidak boleh sama.',
        ]);

        $sourceWallet = Wallet::query()
            ->where('id', $validated['source_wallet_id'])
            ->where('user_id', $user->id)
            ->first();

        $destinationWallet = Wallet::query()
            ->where('id', $validated['destination_wallet_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $sourceWallet || $sourceWallet->is_archived) {
            throw ValidationException::withMessages([
                'source_wallet_id' => [__('messages.transfer.source_invalid')],
            ]);
        }

        if (! $destinationWallet || $destinationWallet->is_archived) {
            throw ValidationException::withMessages([
                'destination_wallet_id' => [__('messages.transfer.destination_invalid')],
            ]);
        }

        // MVP Rule: Transfers only allowed between same currencies
        if ($sourceWallet->currency_code !== $destinationWallet->currency_code) {
            throw ValidationException::withMessages([
                'destination_wallet_id' => [__('messages.transfer.currency_mismatch')],
            ]);
        }

        $sourceBalance = $this->balanceCalculationService->calculateWalletBalance($sourceWallet);

        if (bccomp($this->numericString($validated['amount']), $sourceBalance, 4) === 1) {
            throw ValidationException::withMessages([
                'amount' => [__('messages.transfer.insufficient_balance')],
            ]);
        }

        DB::transaction(function () use ($user, $sourceWallet, $destinationWallet, $validated): void {
            Transfer::create([
                'user_id' => $user->id,
                'source_wallet_id' => $sourceWallet->id,
                'destination_wallet_id' => $destinationWallet->id,
                'amount' => $validated['amount'],
                'currency_code' => $sourceWallet->currency_code,
                'transfer_date' => $validated['transfer_date'],
                'transfer_time' => $validated['transfer_time'] ?? null,
                'note' => isset($validated['note']) ? trim($validated['note']) : null,
            ]);
        });

        return redirect()->route('transfers.index')->with('success', __('messages.transfer.created'));
    }

    public function destroy(Transfer $transfer): RedirectResponse
    {
        Gate::authorize('delete', $transfer);

        $transfer->delete();

        return redirect()->route('transfers.index')->with('success', __('messages.transfer.deleted'));
    }

    /**
     * @return numeric-string
     */
    private function numericString(mixed $value): string
    {
        $value = (string) $value;

        if (! is_numeric($value)) {
            throw ValidationException::withMessages([
                'amount' => [__('validation.numeric', ['attribute' => 'amount'])],
            ]);
        }

        return $value;
    }
}
