<script lang="ts">
    import Card from '@/components/ui/Card.svelte';
    import DatePicker from '@/components/ui/DatePicker.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { t } from '@/lib/i18n';
    import { formatCurrency } from '@/lib/utils';
    import { router } from '@inertiajs/svelte';
    import {
        BarChart3,
        Calendar,
        TrendingDown,
        TrendingUp,
        Wallet,
    } from '@lucide/svelte';

    interface CategorySummary {
        category_name: string;
        category_icon?: string;
        category_color?: string;
        currency_code: string;
        total_amount: string | number;
    }

    interface Props {
        cashFlow?: Record<string, { income: string; expense: string; net: string }>;
        expenseByCategory?: CategorySummary[];
        incomeByCategory?: CategorySummary[];
        wallets?: any[];
        filters?: { start_date?: string; end_date?: string };
    }

    let {
        cashFlow = {},
        expenseByCategory = [],
        incomeByCategory = [],
        wallets = [],
        filters = {},
    }: Props = $props();

    function getInitialDateFilters(): { startDate: string; endDate: string } {
        return {
            startDate: filters.start_date || '',
            endDate: filters.end_date || '',
        };
    }

    const initialDateFilters = getInitialDateFilters();
    let startDate = $state(initialDateFilters.startDate);
    let endDate = $state(initialDateFilters.endDate);

    function handleDateChange() {
        router.get(
            '/app/reports',
            {
                start_date: startDate,
                end_date: endDate,
            },
            { preserveState: true }
        );
    }

    function setQuickFilter(period: 'this_month' | 'last_month' | 'this_year') {
        const now = new Date();
        if (period === 'this_month') {
            const first = new Date(now.getFullYear(), now.getMonth(), 1);
            const last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            startDate = first.toISOString().split('T')[0];
            endDate = last.toISOString().split('T')[0];
        } else if (period === 'last_month') {
            const first = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            const last = new Date(now.getFullYear(), now.getMonth(), 0);
            startDate = first.toISOString().split('T')[0];
            endDate = last.toISOString().split('T')[0];
        } else if (period === 'this_year') {
            const first = new Date(now.getFullYear(), 0, 1);
            const last = new Date(now.getFullYear(), 11, 31);
            startDate = first.toISOString().split('T')[0];
            endDate = last.toISOString().split('T')[0];
        }
        handleDateChange();
    }

    const cashFlowEntries = $derived(Object.entries(cashFlow));

    // Calculate total expense for percentage calculation
    const totalExpense = $derived(
        expenseByCategory.reduce((sum, item) => sum + Number(item.total_amount), 0)
    );

    const totalIncome = $derived(
        incomeByCategory.reduce((sum, item) => sum + Number(item.total_amount), 0)
    );
</script>

<AppLayout title={t('reports.title')}>
    <div class="space-y-6">
        <!-- Header & Quick Range Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                    {t('reports.heading')}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {t('reports.description')}
                </p>
            </div>

            <!-- Quick Filter Presets -->
            <div class="flex items-center gap-1.5 text-xs flex-wrap">
                <button
                    type="button"
                    onclick={() => setQuickFilter('this_month')}
                    class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-medium cursor-pointer"
                >
                    {t('reports.this_month')}
                </button>
                <button
                    type="button"
                    onclick={() => setQuickFilter('last_month')}
                    class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-medium cursor-pointer"
                >
                    {t('reports.last_month')}
                </button>
                <button
                    type="button"
                    onclick={() => setQuickFilter('this_year')}
                    class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-medium cursor-pointer"
                >
                    {t('reports.this_year')}
                </button>
            </div>
        </div>

        <!-- Date Range Filter Box -->
        <Card class="p-4 sm:p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <DatePicker
                    label={t('reports.from_date')}
                    bind:value={startDate}
                    onchange={handleDateChange}
                />
                <DatePicker
                    label={t('reports.to_date')}
                    bind:value={endDate}
                    onchange={handleDateChange}
                    align="right"
                />
            </div>
        </Card>

        <!-- Net Cash Flow Summary Cards (Grouped by Currency) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {#each cashFlowEntries as [currency, flow]}
                <Card class="p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">
                            <span>{t('reports.net_cash_flow')} ({currency})</span>
                            <span class="font-mono uppercase px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">
                                {currency}
                            </span>
                        </div>
                        <div class={`text-2xl font-extrabold font-mono tracking-tight ${Number(flow.net) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'}`}>
                            {Number(flow.net) >= 0 ? '+' : ''}{formatCurrency(flow.net, currency)}
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800/80 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <TrendingUp class="w-3.5 h-3.5 text-emerald-500" />
                                {t('reports.total_income')}:
                            </span>
                            <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400">
                                +{formatCurrency(flow.income, currency)}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <TrendingDown class="w-3.5 h-3.5 text-rose-500" />
                                {t('reports.total_expense')}:
                            </span>
                            <span class="font-bold font-mono text-rose-600 dark:text-rose-400">
                                -{formatCurrency(flow.expense, currency)}
                            </span>
                        </div>
                    </div>
                </Card>
            {:else}
                <div class="col-span-3 text-center py-6 text-xs text-slate-400">
                    {t('reports.no_cash_flow')}
                </div>
            {/each}
        </div>

        <!-- Breakdown: Expense vs Income Categories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Expense Breakdown -->
            <Card class="p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <TrendingDown class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                            {t('reports.expense_by_category')}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {t('reports.expense_description')}
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    {#each expenseByCategory as item}
                        {@const percent = totalExpense > 0 ? (Number(item.total_amount) / totalExpense) * 100 : 0}
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">
                                    {item.category_name}
                                </span>
                                <span class="font-mono font-bold text-slate-900 dark:text-slate-100">
                                    {formatCurrency(item.total_amount, item.currency_code)} ({percent.toFixed(1)}%)
                                </span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                <div
                                    class="h-full bg-rose-500 rounded-full"
                                    style={`width: ${percent}%`}
                                ></div>
                            </div>
                        </div>
                    {:else}
                        <p class="text-xs text-slate-400 text-center py-8">
                            {t('reports.no_expense')}
                        </p>
                    {/each}
                </div>
            </Card>

            <!-- Income Breakdown -->
            <Card class="p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <TrendingUp class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                            {t('reports.income_by_category')}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {t('reports.income_description')}
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    {#each incomeByCategory as item}
                        {@const percent = totalIncome > 0 ? (Number(item.total_amount) / totalIncome) * 100 : 0}
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">
                                    {item.category_name}
                                </span>
                                <span class="font-mono font-bold text-slate-900 dark:text-slate-100">
                                    +{formatCurrency(item.total_amount, item.currency_code)} ({percent.toFixed(1)}%)
                                </span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                <div
                                    class="h-full bg-emerald-500 rounded-full"
                                    style={`width: ${percent}%`}
                                ></div>
                            </div>
                        </div>
                    {:else}
                        <p class="text-xs text-slate-400 text-center py-8">
                            {t('reports.no_income')}
                        </p>
                    {/each}
                </div>
            </Card>
        </div>
    </div>
</AppLayout>
