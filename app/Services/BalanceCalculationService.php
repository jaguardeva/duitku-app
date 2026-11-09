<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;

class BalanceCalculationService
{
    /**
     * Calculate current ledger balance for a single wallet.
     * Formula: opening_balance + income - expense + transfer_in - transfer_out
     */
    public function calculateWalletBalance(Wallet $wallet): string
    {
        $income = (string) Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('type', 'income')
            ->sum('amount');

        $expense = (string) Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('type', 'expense')
            ->sum('amount');

        $transferIn = (string) Transfer::query()
            ->where('destination_wallet_id', $wallet->id)
            ->sum('amount');

        $transferOut = (string) Transfer::query()
            ->where('source_wallet_id', $wallet->id)
            ->sum('amount');

        // High precision calculation using bcmath
        $balance = bcadd((string) $wallet->opening_balance, $income, 4);
        $balance = bcsub($balance, $expense, 4);
        $balance = bcadd($balance, $transferIn, 4);
        $balance = bcsub($balance, $transferOut, 4);

        return $balance;
    }

    /**
     * Get all active and non-archived wallets of a user with computed balances.
     *
     * @return Collection<int, Wallet>
     */
    public function getWalletsWithCalculatedBalance(User $user, bool $includeArchived = false): Collection
    {
        $query = Wallet::query()
            ->where('user_id', $user->id)
            ->orderBy('sort_order')
            ->orderBy('created_at');

        if (! $includeArchived) {
            $query->active();
        }

        $wallets = $query->get();

        foreach ($wallets as $wallet) {
            $wallet->setAttribute('current_balance', $this->calculateWalletBalance($wallet));
        }

        return $wallets;
    }

    /**
     * Group wallet balances by currency code.
     * Adheres to PRD rule: Never sum different currencies into a single total!
     *
     * @return array<string, string>
     */
    public function getBalancesGroupedByCurrency(User $user): array
    {
        $wallets = $this->getWalletsWithCalculatedBalance($user);
        $grouped = [];

        foreach ($wallets as $wallet) {
            $code = $wallet->currency_code;
            $current = $wallet->getAttribute('current_balance');

            if (! isset($grouped[$code])) {
                $grouped[$code] = '0.0000';
            }

            $grouped[$code] = bcadd($grouped[$code], (string) $current, 4);
        }

        return $grouped;
    }

    /**
     * Compute cash flow (income vs expense) for a specific date range.
     * Note: Transfers are strictly excluded from income and expense!
     *
     * @return array<string, array{income: string, expense: string, net: string}>
     */
    public function getCashFlowSummary(
        User $user,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Transaction::query()->where('user_id', $user->id);

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        $transactions = $query->get(['type', 'amount', 'currency_code']);
        $summary = [];

        foreach ($transactions as $tx) {
            $curr = $tx->currency_code;
            if (! isset($summary[$curr])) {
                $summary[$curr] = [
                    'income' => '0.0000',
                    'expense' => '0.0000',
                    'net' => '0.0000',
                ];
            }

            if ($tx->type === 'income') {
                $summary[$curr]['income'] = bcadd($summary[$curr]['income'], (string) $tx->amount, 4);
            } else {
                $summary[$curr]['expense'] = bcadd($summary[$curr]['expense'], (string) $tx->amount, 4);
            }

            $summary[$curr]['net'] = bcsub($summary[$curr]['income'], $summary[$curr]['expense'], 4);
        }

        return $summary;
    }
}
