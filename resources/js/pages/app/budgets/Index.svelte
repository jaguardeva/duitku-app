<script lang="ts">
    import Badge from '@/components/ui/Badge.svelte';
    import Button from '@/components/ui/Button.svelte';
    import Card from '@/components/ui/Card.svelte';
    import ConfirmDialog from '@/components/ui/ConfirmDialog.svelte';
    import CurrencyInput from '@/components/ui/CurrencyInput.svelte';
    import DatePicker from '@/components/ui/DatePicker.svelte';
    import EmptyState from '@/components/ui/EmptyState.svelte';
    import Input from '@/components/ui/Input.svelte';
    import Modal from '@/components/ui/Modal.svelte';
    import Select from '@/components/ui/Select.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { t } from '@/lib/i18n';
    import { formatCurrency, formatDate } from '@/lib/utils';
    import { router, useForm } from '@inertiajs/svelte';
    import {
        AlertTriangle,
        CheckCircle2,
        PieChart,
        Plus,
        Trash2,
    } from '@lucide/svelte';

    interface CategoryItem {
        id: string;
        name: string;
    }

    interface BudgetItem {
        id: string;
        name?: string;
        category_id?: string;
        amount: string | number;
        currency_code: string;
        period_start: string;
        period_end: string;
        warning_threshold_percent: number;
        spent_amount: string | number;
        usage_percent: number;
        actual_percent: number;
        is_warning: boolean;
        is_exceeded: boolean;
        is_active: boolean;
        category?: { name: string };
    }

    interface Props {
        budgets?: BudgetItem[];
        expenseCategories?: CategoryItem[];
        defaultCurrency?: string;
        currentPeriod?: { start_date: string; end_date: string };
    }

    let {
        budgets = [],
        expenseCategories = [],
        defaultCurrency = 'IDR',
        currentPeriod = { start_date: '', end_date: '' },
    }: Props = $props();

    let isCreateModalOpen = $state(false);

    function getInitialFormData() {
        return {
            category_id: '',
            name: '',
            amount: '',
            currency_code: defaultCurrency,
            period_start: currentPeriod.start_date,
            period_end: currentPeriod.end_date,
            warning_threshold_percent: 80,
        };
    }

    const form = useForm(getInitialFormData());

    const categoryOptions = $derived([
        { value: '', label: `${t('transactions.all_categories')} (${t('budgets.global')})` },
        ...expenseCategories.map((c) => ({ value: c.id, label: c.name })),
    ]);

    function openCreateModal() {
        form.reset();
        form.period_start = currentPeriod.start_date;
        form.period_end = currentPeriod.end_date;
        isCreateModalOpen = true;
    }

    function handleCreateSubmit(e: SubmitEvent) {
        e.preventDefault();
        form.post('/app/budgets', {
            onSuccess: () => {
                isCreateModalOpen = false;
                form.reset();
            },
        });
    }

    let budgetToDelete = $state<BudgetItem | null>(null);
    let isDeletingBudget = $state(false);

    function requestDelete(budget: BudgetItem) {
        budgetToDelete = budget;
    }

    function confirmDeleteBudget() {
        if (!budgetToDelete) return;
        isDeletingBudget = true;
        router.delete(`/app/budgets/${budgetToDelete.id}`, {
            onFinish: () => {
                isDeletingBudget = false;
                budgetToDelete = null;
            },
        });
    }
</script>

<AppLayout title={t('budgets.title')}>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                    {t('budgets.heading')}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Tetapkan limit pengeluaran bulanan dan pantau peringatan pemakaian.
                </p>
            </div>
            <Button variant="primary" onclick={openCreateModal} class="text-xs font-semibold">
                <Plus class="w-4 h-4" />
                {t('budgets.create')}
            </Button>
        </div>

        <!-- Budget Cards List -->
        {#if budgets.length === 0}
            <EmptyState
                title={t('empty.no_budgets')}
                description="Tetapkan batas pengeluaran untuk seluruh pengeluaran bulanan atau untuk kategori tertentu."
            >
                {#snippet action()}
                    <Button variant="primary" size="sm" onclick={openCreateModal}>
                        <Plus class="w-4 h-4" />
                        {t('budgets.create_now')}
                    </Button>
                {/snippet}
            </EmptyState>
        {:else}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {#each budgets as b (b.id)}
                    <Card class="p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                                        {b.name || b.category?.name || 'Anggaran Keseluruhan'}
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        Periode: {formatDate(b.period_start)} – {formatDate(b.period_end)}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    {#if b.category}
                                        <Badge variant="default" size="sm">{t('domain.category')}</Badge>
                                    {:else}
                                        <Badge variant="primary" size="sm">{t('budgets.global')}</Badge>
                                    {/if}
                                    <button
                                        type="button"
                                        onclick={() => requestDelete(b)}
                                        class="p-1 rounded text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                                        title={t('actions.delete')}
                                        aria-label={t('actions.delete')}
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="my-4">
                                <div class="flex items-center justify-between text-xs font-bold mb-1.5 font-mono">
                                    <span class="text-slate-700 dark:text-slate-300">
                                        {formatCurrency(b.spent_amount, b.currency_code)}
                                    </span>
                                    <span class="text-slate-500 dark:text-slate-400">
                                        {t('budgets.limit')}: {formatCurrency(b.amount, b.currency_code)} ({b.actual_percent.toFixed(0)}%)
                                    </span>
                                </div>
                                <div class="w-full h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div
                                        class={`h-full rounded-full transition-all duration-300 ${
                                            b.is_exceeded
                                                ? 'bg-rose-600'
                                                : b.is_warning
                                                ? 'bg-amber-400'
                                                : 'bg-[#003A70] dark:bg-[#FFB700]'
                                        }`}
                                        style={`width: ${Math.min(100, b.usage_percent)}%`}
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Alert Notice -->
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                            {#if b.is_exceeded}
                                <span class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400 font-semibold">
                                    <AlertTriangle class="w-4 h-4 shrink-0" />
                                    {t('budgets.exceeded')}
                                </span>
                            {:else if b.is_warning}
                                <span class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-semibold">
                                    <AlertTriangle class="w-4 h-4 shrink-0" />
                                    {t('budgets.warning', { percent: b.warning_threshold_percent })}
                                </span>
                            {:else}
                                <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-medium">
                                    <CheckCircle2 class="w-4 h-4 shrink-0" />
                                    {t('budgets.safe')}
                                </span>
                            {/if}

                            <span class="text-slate-400 font-mono">
                                {t('budgets.remaining')}: {formatCurrency(Math.max(0, Number(b.amount) - Number(b.spent_amount)), b.currency_code)}
                            </span>
                        </div>
                    </Card>
                {/each}
            </div>
        {/if}
    </div>

    <!-- Create Budget Modal -->
    <Modal
        bind:open={isCreateModalOpen}
        title={t('budgets.create_monthly')}
        size="md"
    >
        <form onsubmit={handleCreateSubmit} class="space-y-4">
            <Select
                label={t('budgets.scope')}
                options={categoryOptions}
                bind:value={form.category_id}
                error={form.errors.category_id}
            />

            <Input
                label={t('budgets.name')}
                placeholder={t('budgets.name_placeholder')}
                maxlength={120}
                bind:value={form.name}
                error={form.errors.name}
            />

            <CurrencyInput
                label={t('budgets.maximum')}
                placeholder="0"
                required
                bind:value={form.amount}
                error={form.errors.amount}
            />

            <div class="grid grid-cols-2 gap-3">
                <DatePicker
                    label={t('reports.from_date')}
                    required
                    bind:value={form.period_start}
                    error={form.errors.period_start}
                />

                <DatePicker
                    label={t('reports.to_date')}
                    required
                    bind:value={form.period_end}
                    error={form.errors.period_end}
                    align="right"
                />
            </div>

            <Input
                label={t('budgets.warning_threshold')}
                type="number"
                min="1"
                max="100"
                hint={t('budgets.warning_hint')}
                bind:value={form.warning_threshold_percent}
                error={form.errors.warning_threshold_percent}
            />

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                <Button
                    type="button"
                    variant="outline"
                    onclick={() => (isCreateModalOpen = false)}
                >
                    {t('common.cancel')}
                </Button>
                <Button
                    type="submit"
                    variant="primary"
                    loading={form.processing}
                >
                    {t('common.save')}
                </Button>
            </div>
        </form>
    </Modal>

    <!-- Custom Delete Budget Confirmation Dialog -->
    <ConfirmDialog
        open={!!budgetToDelete}
        title={`${t('actions.delete')} ${t('navigation.budgets')}`}
        message={t('budgets.delete_confirm', { scope: budgetToDelete?.category ? `untuk kategori "${budgetToDelete.category.name}"` : t('budgets.global').toLowerCase() })}
        confirmText={`${t('actions.confirm')}, ${t('actions.delete')}`}
        cancelText={t('common.cancel')}
        variant="danger"
        loading={isDeletingBudget}
        onconfirm={confirmDeleteBudget}
        oncancel={() => (budgetToDelete = null)}
    />
</AppLayout>
