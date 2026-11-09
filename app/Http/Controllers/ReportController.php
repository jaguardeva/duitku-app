<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\BalanceCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(
        protected BalanceCalculationService $balanceService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = Carbon::now($user->timezone ?: 'UTC');

        $startDate = $request->input('start_date', $now->copy()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', $now->copy()->endOfMonth()->format('Y-m-d'));

        // Cash flow summary per currency
        $cashFlow = $this->balanceService->getCashFlowSummary($user, $startDate, $endDate);

        // Expense breakdown by category
        $expenseByCategory = Transaction::query()
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select(
                'categories.name as category_name',
                'categories.icon as category_icon',
                'categories.color_token as category_color',
                'transactions.currency_code',
                DB::raw('SUM(transactions.amount) as total_amount')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.icon', 'categories.color_token', 'transactions.currency_code')
            ->orderByDesc('total_amount')
            ->get();

        // Income breakdown by category
        $incomeByCategory = Transaction::query()
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'income')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select(
                'categories.name as category_name',
                'categories.icon as category_icon',
                'categories.color_token as category_color',
                'transactions.currency_code',
                DB::raw('SUM(transactions.amount) as total_amount')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.icon', 'categories.color_token', 'transactions.currency_code')
            ->orderByDesc('total_amount')
            ->get();

        // Wallet balance distribution
        $wallets = $this->balanceService->getWalletsWithCalculatedBalance($user);

        return Inertia::render('app/reports/Index', [
            'cashFlow' => $cashFlow,
            'expenseByCategory' => $expenseByCategory,
            'incomeByCategory' => $incomeByCategory,
            'wallets' => $wallets,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }
}
