<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use App\Services\BalanceCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected BalanceCalculationService $balanceService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $userTimezone = $user->timezone ?: 'UTC';

        // Calculate current calendar month range in user timezone
        $now = Carbon::now($userTimezone);
        $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

        // Wallets with computed ledger balances
        $wallets = $this->balanceService->getWalletsWithCalculatedBalance($user);

        // Grouped balances per currency
        $balancesByCurrency = $this->balanceService->getBalancesGroupedByCurrency($user);

        // Cash flow for current month
        $cashFlow = $this->balanceService->getCashFlowSummary($user, $startOfMonth, $endOfMonth);

        // Recent transactions (latest 8)
        $recentTransactions = Transaction::query()
            ->where('user_id', $user->id)
            ->with(['wallet:id,name,currency_code', 'category:id,name,icon,color_token'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // Active budgets with progress calculation
        $budgets = Budget::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('period_start', '<=', $endOfMonth)
            ->where('period_end', '>=', $startOfMonth)
            ->with('category:id,name,icon,color_token')
            ->get()
            ->map(function (Budget $b) use ($startOfMonth, $endOfMonth): Budget {
                $query = Transaction::query()
                    ->where('user_id', $b->user_id)
                    ->where('type', 'expense')
                    ->where('currency_code', $b->currency_code)
                    ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth]);

                if ($b->category_id) {
                    $query->where('category_id', $b->category_id);
                }

                $spent = (string) $query->sum('amount');
                $b->setAttribute('spent_amount', $spent);

                $percent = $b->amount > 0 ? (float) bcdiv(bcmul($spent, '100', 4), (string) $b->amount, 2) : 0;
                $b->setAttribute('usage_percent', min(100, $percent));
                $b->setAttribute('is_warning', $percent >= $b->warning_threshold_percent);
                $b->setAttribute('is_exceeded', $percent > 100);

                return $b;
            });

        return Inertia::render('app/Dashboard', [
            'wallets' => $wallets,
            'balancesByCurrency' => $balancesByCurrency,
            'cashFlow' => $cashFlow,
            'recentTransactions' => $recentTransactions,
            'budgets' => $budgets,
            'currentPeriod' => [
                'month_name' => $now->translatedFormat('F Y'),
                'start_date' => $startOfMonth,
                'end_date' => $endOfMonth,
            ],
        ]);
    }
}
