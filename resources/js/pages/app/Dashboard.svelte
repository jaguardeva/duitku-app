<script lang="ts">
    import QuickActionButton from '@/components/domain/QuickActionButton.svelte';
    import TransactionListItem from '@/components/domain/TransactionListItem.svelte';
    import WalletPocketCard from '@/components/domain/WalletPocketCard.svelte';
    import BalanceToggle from '@/components/ui/BalanceToggle.svelte';
    import Card from '@/components/ui/Card.svelte';
    import EmptyState from '@/components/ui/EmptyState.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { balanceVisibility } from '@/lib/balanceVisibility.svelte';
    import { t } from '@/lib/i18n';
    import { formatCurrency } from '@/lib/utils';
    import { Link, router } from '@inertiajs/svelte';
    import {
        AlertTriangle,
        ArrowDownLeft,
        ArrowLeftRight,
        ArrowUpRight,
        ChevronRight,
        PieChart,
        Plus,
        TrendingDown,
        TrendingUp,
        Wallet,
    } from '@lucide/svelte';

    interface Props {
        wallets?: any[];
        balancesByCurrency?: Record<string, string>;
        cashFlow?: Record<string, { income: string; expense: string; net: string }>;
        recentTransactions?: any[];
        budgets?: any[];
        currentPeriod?: { month_name: string; start_date: string; end_date: string };
    }

    let {
        wallets = [],
        balancesByCurrency = {},
        cashFlow = {},
        recentTransactions = [],
        budgets = [],
        currentPeriod = { month_name: 'Bulan Ini', start_date: '', end_date: '' },
    }: Props = $props();

    const currencies = $derived(Object.keys(balancesByCurrency));
</script>

<AppLayout title={t('navigation.dashboard')}>
    <div class="space-y-6 sm:space-y-8">
        <!-- Top Section: Total Balance Overview (grouped by currency) & Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
            <!-- Total Balance Card -->
            <div class="md:col-span-2 rounded-2xl bg-gradient-to-br from-[#003A70] via-[#0C3B67] to-[#002B54] text-white p-5 sm:p-7 shadow-lg shadow-[#003A70]/20 relative overflow-hidden border border-[#003A70]/40 flex flex-col justify-between">
                <div class="absolute -right-6 -bottom-6 w-36 h-36 rounded-full bg-white/5 pointer-events-none"></div>
                <div class="absolute right-12 top-6 w-20 h-20 rounded-full bg-[#FFB700]/15 pointer-events-none"></div>

                <div class="relative z-10 flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                            <div class="inline-flex items-center gap-2">
                                <span class="text-xs font-semibold tracking-wider uppercase text-[#FFD200]">
                                    {t('dashboard.total_balance')}
                                </span>
                                <BalanceToggle variant="hero" size="sm" />
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 dark:bg-[#FFB700]/20 text-[#FFD200] border border-[#FFD200]/40 font-semibold text-xs shadow-xs backdrop-blur-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FFD200] animate-pulse"></span>
                                <span>{currentPeriod.month_name}</span>
                            </div>
                        </div>

                        {#if currencies.length === 0}
                            <div class="mt-1">
                                <span class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight font-mono">
                                    {balanceVisibility.format(0, 'IDR')}
                                </span>
                            </div>
                            <p class="text-xs text-blue-100/90 mt-2">
                                {t('dashboard.no_wallet_description')}
                            </p>
                        {:else}
                            <div class="space-y-1.5 mt-2">
                                {#each currencies as curr}
                                    <div>
                                        <span class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight font-mono break-words">
                                            {balanceVisibility.format(balancesByCurrency[curr], curr)}
                                        </span>
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>

                    <!-- Quick Cash Flow Bar inside summary -->
                    <div class="mt-6 pt-5 border-t border-white/15 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        {#each Object.entries(cashFlow) as [curr, flow]}
                            <div class="flex items-center gap-2.5 bg-white/5 sm:bg-transparent p-2.5 sm:p-0 rounded-xl">
                                <div class="w-8 h-8 rounded-lg bg-emerald-400/20 text-emerald-300 flex items-center justify-center shrink-0 border border-emerald-400/20">
                                    <TrendingUp class="w-4 h-4" />
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[11px] text-blue-100/80 block truncate">{t('domain.income')} ({curr})</span>
                                    <span class="text-xs sm:text-sm font-bold font-mono text-emerald-300 truncate block">
                                        +{balanceVisibility.format(flow.income, curr)}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 bg-white/5 sm:bg-transparent p-2.5 sm:p-0 rounded-xl">
                                <div class="w-8 h-8 rounded-lg bg-rose-400/20 text-rose-300 flex items-center justify-center shrink-0 border border-rose-400/20">
                                    <TrendingDown class="w-4 h-4" />
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[11px] text-blue-100/80 block truncate">{t('domain.expense')} ({curr})</span>
                                    <span class="text-xs sm:text-sm font-bold font-mono text-rose-300 truncate block">
                                        -{balanceVisibility.format(flow.expense, curr)}
                                    </span>
                                </div>
                            </div>
                        {:else}
                            <div class="text-xs text-blue-100/80 col-span-full">
                                {t('dashboard.cash_flow_empty', { period: currentPeriod.month_name })}
                            </div>
                        {/each}
                    </div>
                </div>
            </div>

            <!-- Quick Action Panel Card -->
            <Card class="p-4 sm:p-5">
                <div class="grid grid-cols-3 gap-1">
                        <QuickActionButton
                            label={t('domain.income')}
                            icon={ArrowDownLeft}
                            variant="income"
                            href="/app/transactions/create?type=income"
                        />
                        <QuickActionButton
                            label={t('domain.expense')}
                            icon={ArrowUpRight}
                            variant="expense"
                            href="/app/transactions/create?type=expense"
                        />
                        <QuickActionButton
                            label={t('navigation.transfers')}
                            icon={ArrowLeftRight}
                            variant="transfer"
                            href="/app/transfers"
                        />
                        <QuickActionButton
                            label={t('domain.wallet')}
                            icon={Wallet}
                            variant="wallet"
                            href="/app/wallets"
                        />
                        <QuickActionButton
                            label={t('actions.manage')}
                            icon={PieChart}
                            variant="budget"
                            href="/app/budgets"
                        />
                </div>
            </Card>
        </div>

        <!-- Section 2: Kantong Saku (Wallets) -->
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                        {t('dashboard.my_wallets')}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {t('wallets.create_hint')}
                    </p>
                </div>
                <Link
                    href="/app/wallets"
                    class="inline-flex items-center justify-center gap-1.5 self-start sm:self-auto px-3 py-1.5 rounded-lg text-xs font-semibold border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-[#003A70] dark:hover:border-[#FFB700] transition-colors shadow-2xs"
                >
                    <Plus class="w-3.5 h-3.5 text-[#003A70] dark:text-[#FFB700]" />
                    <span>{t('actions.add')} {t('domain.wallet')}</span>
                </Link>
            </div>

            {#if wallets.length === 0}
                <EmptyState
                    title={t('empty.no_wallet')}
                    description={t('empty.no_wallet_description')}
                >
                    {#snippet action()}
                        <Link
                            href="/app/wallets"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold bg-[#003A70] hover:bg-[#002B54] text-white shadow-xs transition-colors"
                        >
                            <Plus class="w-4 h-4 text-[#FFB700]" />
                            <span>{t('actions.create')} {t('domain.wallet')}</span>
                        </Link>
                    {/snippet}
                </EmptyState>
            {:else}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {#each wallets as wallet (wallet.id)}
                        <WalletPocketCard
                            {wallet}
                            onclick={() => router.visit('/app/wallets')}
                        />
                    {/each}
                </div>
            {/if}
        </div>

        <!-- Section 3: Budget Highlights & Recent Transactions Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Left 2 Cols: Recent Transactions -->
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                            {t('dashboard.recent_transactions')}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Aktivitas pencatatan terbaru pada akun Anda.
                        </p>
                    </div>
                    <Link
                        href="/app/transactions"
                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-[#003A70] hover:text-[#0C3B67] dark:text-[#FFD200] dark:hover:text-[#FFB700] hover:bg-[#003A70]/10 dark:hover:bg-[#FFB700]/15 transition-colors inline-flex items-center gap-1"
                    >
                        <span>{t('actions.view_all')}</span>
                        <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <Card class="p-2 sm:p-3 divide-y divide-slate-100 dark:divide-slate-800">
                    {#each recentTransactions as tx (tx.id)}
                        <TransactionListItem
                            transaction={tx}
                            onclick={() => router.visit('/app/transactions')}
                        />
                    {:else}
                        <div class="py-10 text-center">
                            <EmptyState
                                title={t('empty.no_transactions')}
                                description={t('empty.no_transaction_description')}
                            >
                                {#snippet action()}
                                    <Link
                                        href="/app/transactions/create?type=expense"
                                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold bg-[#003A70] hover:bg-[#002B54] text-white shadow-xs transition-colors"
                                    >
                                        <Plus class="w-4 h-4 text-[#FFB700]" />
                                        <span>{t('transactions.create_title')}</span>
                                    </Link>
                                {/snippet}
                            </EmptyState>
                        </div>
                    {/each}
                </Card>
            </div>

            <!-- Right 1 Col: Monthly Budgets Progress -->
            <div>
                <div class="flex items-center justify-between gap-2 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                            {t('dashboard.monthly_budget')}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {t('budgets.warning_hint')}
                        </p>
                    </div>
                    <Link
                        href="/app/budgets"
                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-[#003A70] hover:text-[#0C3B67] dark:text-[#FFD200] dark:hover:text-[#FFB700] hover:bg-[#003A70]/10 dark:hover:bg-[#FFB700]/15 transition-colors inline-flex items-center gap-1"
                    >
                        <span>{t('actions.manage')}</span>
                        <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <Card class="p-5 space-y-5">
                    {#each budgets as b (b.id)}
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="font-semibold text-slate-800 dark:text-slate-200 truncate">
                                    {b.name || b.category?.name || 'Anggaran Keseluruhan'}
                                </span>
                                <span class="font-mono text-slate-500 dark:text-slate-400">
                                    {b.actual_percent ? b.actual_percent.toFixed(0) : (b.usage_percent ?? 0).toFixed(0)}%
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full h-2.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                <div
                                    class={`h-full rounded-full transition-all duration-300 ${
                                        b.is_exceeded
                                            ? 'bg-rose-500'
                                            : b.is_warning
                                            ? 'bg-amber-400'
                                            : 'bg-[#003A70] dark:bg-[#FFB700]'
                                    }`}
                                    style={`width: ${Math.min(100, b.usage_percent)}%`}
                                ></div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">
                                <span>{t('dashboard.spent')}: {formatCurrency(b.spent_amount, b.currency_code)}</span>
                                <span class="font-mono">{t('dashboard.limit')}: {formatCurrency(b.amount, b.currency_code)}</span>
                            </div>

                            {#if b.is_exceeded}
                                <div class="mt-2 flex items-center gap-1.5 text-[11px] font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/50 p-2 rounded-lg border border-rose-200/60 dark:border-rose-900/40">
                                    <AlertTriangle class="w-3.5 h-3.5 shrink-0 text-rose-600 dark:text-rose-400" />
                                    <span>{t('dashboard.budget_warning')}</span>
                                </div>
                            {:else if b.is_warning}
                                <div class="mt-2 flex items-center gap-1.5 text-[11px] font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 p-2 rounded-lg border border-amber-200/60 dark:border-amber-900/40">
                                    <AlertTriangle class="w-3.5 h-3.5 shrink-0 text-amber-600 dark:text-amber-400" />
                                    <span>Mendekati batas peringatan ({b.warning_threshold_percent}%)</span>
                                </div>
                            {/if}
                        </div>
                    {:else}
                        <div class="text-center py-6">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                                {t('dashboard.no_active_budget')}
                            </p>
                            <Link
                                href="/app/budgets"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                            >
                                <Plus class="w-3.5 h-3.5 text-[#003A70] dark:text-[#FFB700]" />
                                <span>{t('actions.create')} {t('navigation.budgets')}</span>
                            </Link>
                        </div>
                    {/each}
                </Card>
            </div>
        </div>
    </div>
</AppLayout>
