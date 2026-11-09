<script lang="ts">
    import TransactionListItem from '@/components/domain/TransactionListItem.svelte';
    import Button from '@/components/ui/Button.svelte';
    import Card from '@/components/ui/Card.svelte';
    import EmptyState from '@/components/ui/EmptyState.svelte';
    import DatePicker from '@/components/ui/DatePicker.svelte';
    import Input from '@/components/ui/Input.svelte';
    import Modal from '@/components/ui/Modal.svelte';
    import Select from '@/components/ui/Select.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { t } from '@/lib/i18n';
    import { debounce } from '@/lib/utils';
    import { Link, router } from '@inertiajs/svelte';
    import {
        ArrowDownLeft,
        ArrowUpRight,
        Filter,
        Plus,
        RotateCcw,
        Search,
    } from '@lucide/svelte';

    interface Props {
        transactions?: {
            data: any[];
            current_page: number;
            last_page: number;
            prev_page_url?: string;
            next_page_url?: string;
            total: number;
        };
        wallets?: any[];
        categories?: any[];
        filters?: {
            start_date?: string;
            end_date?: string;
            type?: string;
            wallet_id?: string;
            category_id?: string;
            search?: string;
        };
    }

    let {
        transactions = { data: [], current_page: 1, last_page: 1, total: 0 },
        wallets = [],
        categories = [],
        filters = {},
    }: Props = $props();

    function getInitialFilters(): {
        startDate: string;
        endDate: string;
        type: string;
        walletId: string;
        categoryId: string;
        search: string;
    } {
        return {
            startDate: filters.start_date || '',
            endDate: filters.end_date || '',
            type: filters.type || '',
            walletId: filters.wallet_id || '',
            categoryId: filters.category_id || '',
            search: filters.search || '',
        };
    }

    const initialFilters = getInitialFilters();
    let startDate = $state(initialFilters.startDate);
    let endDate = $state(initialFilters.endDate);
    let type = $state(initialFilters.type);
    let walletId = $state(initialFilters.walletId);
    let categoryId = $state(initialFilters.categoryId);
    let search = $state(initialFilters.search);
    let isMobileFilterOpen = $state(false);

    const debouncedApplyFilters = debounce(() => {
        applyFilters();
    }, 350);

    function handleSearchInput(e: Event) {
        const target = e.target as HTMLInputElement;
        search = target.value;
        debouncedApplyFilters();
    }

    const hasActiveFilters = $derived(
        !!(search.trim() || type || walletId || categoryId || startDate || endDate)
    );

    const activeFiltersCount = $derived(
        [type, walletId, categoryId, startDate, endDate].filter(Boolean).length
    );

    function applyFilters() {
        router.get(
            '/app/transactions',
            {
                start_date: startDate || undefined,
                end_date: endDate || undefined,
                type: type || undefined,
                wallet_id: walletId || undefined,
                category_id: categoryId || undefined,
                search: search || undefined,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    }

    function resetFilters() {
        startDate = '';
        endDate = '';
        type = '';
        walletId = '';
        categoryId = '';
        search = '';
        isMobileFilterOpen = false;
        router.get('/app/transactions');
    }

    const typeOptions = [
        { value: '', label: 'Semua Tipe' },
        { value: 'income', label: `${t('domain.income')} (+)` },
        { value: 'expense', label: `${t('domain.expense')} (-)` },
    ];

    const walletOptions = $derived([
        { value: '', label: t('transactions.all_wallets') },
        ...wallets.map((w) => ({ value: w.id, label: w.name })),
    ]);

    const categoryOptions = $derived([
        { value: '', label: t('transactions.all_categories') },
        ...categories.map((c) => ({ value: c.id, label: `${c.name} (${c.type === 'income' ? t('domain.income') : t('domain.expense')})` })),
    ]);
</script>

<AppLayout title={t('transactions.title')}>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                    {t('transactions.title')}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Daftar lengkap seluruh catatan pemasukan dan pengeluaran Anda.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Link href="/app/transactions/create?type=income">
                    <Button variant="outline" size="sm" class="text-emerald-600 border-emerald-300 dark:border-emerald-800">
                        <ArrowDownLeft class="w-3.5 h-3.5" />
                        {t('domain.income')}
                    </Button>
                </Link>
                <Link href="/app/transactions/create?type=expense">
                    <Button variant="primary" size="sm">
                        <Plus class="w-3.5 h-3.5" />
                        {t('domain.expense')}
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Filter Card -->
        <Card class="p-4 sm:p-5">
            <!-- Filter Header with Conditional Reset Button -->
            <div class="hidden md:flex items-center justify-between mb-3.5 pb-2.5 border-b border-slate-100 dark:border-slate-800/80">
                <div class="flex items-center gap-2">
                    <Filter class="w-4 h-4 text-slate-400" />
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{t('transactions.filter')}</span>
                    {#if activeFiltersCount > 0}
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#003A70]/10 text-[#003A70] dark:bg-[#FFB700]/20 dark:text-[#FFD200]">
                            {t('transactions.active_filters', { count: activeFiltersCount })}
                        </span>
                    {/if}
                </div>

                {#if hasActiveFilters}
                    <button
                        type="button"
                        onclick={resetFilters}
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 transition-colors cursor-pointer animate-in fade-in duration-150"
                        title="Reset semua filter aktif"
                    >
                        <RotateCcw class="w-3.5 h-3.5" />
                        {t('actions.reset_filter')}
                    </button>
                {/if}
            </div>

            <div class="md:hidden space-y-3">
                <Input
                    placeholder={t('transactions.search')}
                    bind:value={search}
                    oninput={handleSearchInput}
                    onchange={applyFilters}
                >
                    {#snippet leadingIcon()}
                        <Search class="w-4 h-4" />
                    {/snippet}
                </Input>

                <div class="flex items-center gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="flex-1 justify-center"
                        onclick={() => (isMobileFilterOpen = true)}
                    >
                        <Filter class="w-4 h-4" />
                        {t('transactions.open_filters')}
                        {#if activeFiltersCount > 0}
                            <span class="inline-flex min-w-5 h-5 items-center justify-center rounded-full bg-[#003A70]/10 px-1.5 text-[10px] font-bold text-[#003A70] dark:bg-[#FFB700]/20 dark:text-[#FFD200]">
                                {activeFiltersCount}
                            </span>
                        {/if}
                    </Button>

                    {#if hasActiveFilters}
                        <Button
                            type="button"
                            variant="ghost"
                            class="shrink-0 px-2 text-rose-600 dark:text-rose-400"
                            onclick={resetFilters}
                            title={t('actions.reset_filter')}
                            aria-label={t('actions.reset_filter')}
                        >
                            <RotateCcw class="w-4 h-4" />
                        </Button>
                    {/if}
                </div>
            </div>

            <div class="hidden md:grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search Text (span 2 cols) -->
                <div class="lg:col-span-2">
                    <Input
                        placeholder={t('transactions.search')}
                        bind:value={search}
                        oninput={handleSearchInput}
                        onchange={applyFilters}
                    >
                        {#snippet leadingIcon()}
                            <Search class="w-4 h-4" />
                        {/snippet}
                    </Input>
                </div>

                <!-- Type Selector -->
                <div>
                    <Select
                        options={typeOptions}
                        bind:value={type}
                        onchange={applyFilters}
                    />
                </div>

                <!-- Wallet Selector -->
                <div>
                    <Select
                        options={walletOptions}
                        bind:value={walletId}
                        onchange={applyFilters}
                    />
                </div>

                <!-- Category Selector -->
                <div>
                    <Select
                        options={categoryOptions}
                        bind:value={categoryId}
                        onchange={applyFilters}
                    />
                </div>
            </div>

            <div class="hidden md:grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                <DatePicker
                        label={t('reports.from_date')}
                    bind:value={startDate}
                    onchange={applyFilters}
                />
                <DatePicker
                        label={t('reports.to_date')}
                    bind:value={endDate}
                    onchange={applyFilters}
                    align="right"
                />
            </div>
        </Card>

        <!-- Mobile Filter Modal -->
        <Modal
            bind:open={isMobileFilterOpen}
            title={t('transactions.filter')}
            size="md"
        >
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {activeFiltersCount > 0 ? t('transactions.active_filters', { count: activeFiltersCount }) : t('transactions.no_active_filters')}
                    </span>
                    {#if hasActiveFilters}
                        <button
                            type="button"
                            onclick={resetFilters}
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300"
                        >
                            <RotateCcw class="w-3.5 h-3.5" />
                            {t('actions.reset_filter')}
                        </button>
                    {/if}
                </div>

                <Select
                    options={typeOptions}
                    bind:value={type}
                    onchange={applyFilters}
                />
                <Select
                    options={walletOptions}
                    bind:value={walletId}
                    onchange={applyFilters}
                />
                <Select
                    options={categoryOptions}
                    bind:value={categoryId}
                    onchange={applyFilters}
                />
                <DatePicker
                    label={t('reports.from_date')}
                    bind:value={startDate}
                    onchange={applyFilters}
                />
                <DatePicker
                    label={t('reports.to_date')}
                    bind:value={endDate}
                    onchange={applyFilters}
                />
            </div>
        </Modal>

        <!-- Transaction List -->
        <Card class="p-2 divide-y divide-slate-100 dark:divide-slate-800">
            {#each transactions.data as tx (tx.id)}
                <TransactionListItem
                    transaction={tx}
                    onclick={() => router.visit(`/app/transactions/${tx.id}/edit`)}
                />
            {:else}
                <div class="py-12">
                    <EmptyState
                        title={t('empty.no_transactions')}
                        description={t('empty.no_transaction_description')}
                    >
                        {#snippet action()}
                            <Button variant="outline" size="sm" onclick={resetFilters}>
                                {t('actions.reset_filter')}
                            </Button>
                        {/snippet}
                    </EmptyState>
                </div>
            {/each}
        </Card>

        <!-- Pagination Bar -->
        {#if transactions.last_page > 1}
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 px-2">
                <span>
                    Menampilkan {transactions.data.length} dari {transactions.total} transaksi
                </span>
                <div class="flex items-center gap-1.5">
                    {#if transactions.prev_page_url}
                        <Link href={transactions.prev_page_url}>
                            <Button variant="outline" size="sm">{t('actions.previous')}</Button>
                        </Link>
                    {/if}
                    <span class="px-3 py-1 font-semibold text-slate-800 dark:text-slate-200">
                        {transactions.current_page} / {transactions.last_page}
                    </span>
                    {#if transactions.next_page_url}
                        <Link href={transactions.next_page_url}>
                            <Button variant="outline" size="sm">{t('actions.next')}</Button>
                        </Link>
                    {/if}
                </div>
            </div>
        {/if}
    </div>
</AppLayout>
