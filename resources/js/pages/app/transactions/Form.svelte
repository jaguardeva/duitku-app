<script lang="ts">
    import Button from '@/components/ui/Button.svelte';
    import Card from '@/components/ui/Card.svelte';
    import ConfirmDialog from '@/components/ui/ConfirmDialog.svelte';
    import CurrencyInput from '@/components/ui/CurrencyInput.svelte';
    import DatePicker from '@/components/ui/DatePicker.svelte';
    import Input from '@/components/ui/Input.svelte';
    import Select from '@/components/ui/Select.svelte';
    import TimePicker from '@/components/ui/TimePicker.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { t } from '@/lib/i18n';
    import { Link, router, useForm } from '@inertiajs/svelte';
    import {
        ArrowDownLeft,
        ArrowLeft,
        ArrowUpRight,
        Trash2,
    } from '@lucide/svelte';

    interface WalletItem {
        id: string;
        name: string;
        currency_code: string;
        type?: string;
    }

    interface CategoryItem {
        id: string;
        name: string;
        type: string;
        icon?: string;
        color_token?: string;
    }

    interface Props {
        transaction?: any;
        wallets?: WalletItem[];
        categories?: CategoryItem[];
        defaultType?: string;
        defaultWalletId?: string;
    }

    let {
        transaction,
        wallets = [],
        categories = [],
        defaultType = 'expense',
        defaultWalletId,
    }: Props = $props();

    const isEditing = $derived(!!transaction);
    const today = new Date().toISOString().split('T')[0];

    function getInitialFormData() {
        return {
            type: transaction?.type || defaultType,
            wallet_id: transaction?.wallet_id || defaultWalletId || (wallets[0]?.id ?? ''),
            category_id: transaction?.category_id || '',
            amount: transaction?.amount ? parseFloat(transaction.amount) : '',
            title: transaction?.title || '',
            notes: transaction?.notes || '',
            transaction_date: transaction?.transaction_date || today,
            transaction_time: transaction?.transaction_time || '',
        };
    }

    const form = useForm(getInitialFormData());

    // Categories filtered by current form type
    const filteredCategories = $derived(
        categories.filter((c) => c.type === form.type)
    );

    // Wallet options
    const walletOptions = $derived(
        wallets.map((w) => ({ value: w.id, label: w.name }))
    );

    // Category options
    const categoryOptions = $derived([
        { value: '', label: `-- ${t('domain.category')} --` },
        ...filteredCategories.map((c) => ({ value: c.id, label: c.name })),
    ]);

    // Active wallet currency code
    const selectedWallet = $derived(
        wallets.find((w) => w.id === form.wallet_id)
    );

    function handleTypeChange(newType: 'income' | 'expense') {
        form.type = newType;
        // Reset category if it doesn't match new type
        const catStillValid = filteredCategories.some((c) => c.id === form.category_id);
        if (!catStillValid) {
            form.category_id = '';
        }
    }

    function handleSubmit(e: SubmitEvent) {
        e.preventDefault();
        if (isEditing) {
            form.put(`/app/transactions/${transaction.id}`);
        } else {
            form.post('/app/transactions');
        }
    }

    let isDeleteConfirmOpen = $state(false);
    let isDeleting = $state(false);

    function handleDeleteClick() {
        isDeleteConfirmOpen = true;
    }

    function confirmDelete() {
        if (!transaction) return;
        isDeleting = true;
        router.delete(`/app/transactions/${transaction.id}`, {
            onFinish: () => {
                isDeleting = false;
                isDeleteConfirmOpen = false;
            },
        });
    }
</script>

<AppLayout title={isEditing ? t('transactions.edit_title') : t('transactions.create_title')}>
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Back Link & Title Header -->
        <div class="flex items-center justify-between">
            <Link
                href="/app/transactions"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors"
            >
                <ArrowLeft class="w-4 h-4" />
                {t('transactions.back_history')}
            </Link>

            {#if isEditing}
                <button
                    type="button"
                    onclick={handleDeleteClick}
                    class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 inline-flex items-center gap-1 cursor-pointer"
                >
                    <Trash2 class="w-3.5 h-3.5" />
                    {t('actions.delete')} {t('transactions.entity')}
                </button>
            {/if}
        </div>

        <Card class="p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                    {isEditing ? t('transactions.edit_title') : t('transactions.create_title')}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {t('transactions.new_description')}
                </p>
            </div>

            <!-- Transaction Type Switcher -->
            <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-slate-100 dark:bg-slate-800 mb-6">
                <button
                    type="button"
                    onclick={() => handleTypeChange('expense')}
                    class={`py-2.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer ${
                        form.type === 'expense'
                            ? 'bg-rose-600 text-white shadow-sm'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                    }`}
                >
                    <ArrowUpRight class="w-4 h-4" />
                    {t('domain.expense')} (-)
                </button>
                <button
                    type="button"
                    onclick={() => handleTypeChange('income')}
                    class={`py-2.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer ${
                        form.type === 'income'
                            ? 'bg-emerald-600 text-white shadow-sm'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                    }`}
                >
                    <ArrowDownLeft class="w-4 h-4" />
                    {t('domain.income')} (+)
                </button>
            </div>

            <form onsubmit={handleSubmit} class="space-y-5">
                <!-- Amount Field with CurrencyInput -->
                <CurrencyInput
                    id="tx-amount"
                    label={t('transactions.amount')}
                    size="lg"
                    placeholder="0"
                    required
                    bind:value={form.amount}
                    error={form.errors.amount}
                />

                <!-- Wallet and Category Selectors -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Select
                        label={t('transactions.source_wallet')}
                        options={walletOptions}
                        required
                        bind:value={form.wallet_id}
                        error={form.errors.wallet_id}
                    />

                    <Select
                        label={t('transactions.category')}
                        options={categoryOptions}
                        required
                        bind:value={form.category_id}
                        error={form.errors.category_id}
                    />
                </div>

                <!-- Title / Description -->
                <Input
                    label={t('transactions.title_label')}
                    placeholder={t('transactions.title_placeholder')}
                    required
                    maxlength={160}
                    bind:value={form.title}
                    error={form.errors.title}
                />

                <!-- Calendar Date and Optional Time -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <DatePicker
                        label={t('transactions.date')}
                        required
                        bind:value={form.transaction_date}
                        error={form.errors.transaction_date}
                    />

                    <TimePicker
                        label={t('transactions.time')}
                        bind:value={form.transaction_time}
                        error={form.errors.transaction_time}
                        align="right"
                    />
                </div>

                <!-- Optional Notes -->
                <div>
                    <label for="tx-notes" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {t('transactions.notes')}
                    </label>
                    <textarea
                        id="tx-notes"
                        rows="3"
                        placeholder={t('transactions.notes_placeholder')}
                        bind:value={form.notes}
                        class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#003A70]/25 focus:border-[#003A70] dark:focus:ring-[#FFB700]/25 dark:focus:border-[#FFB700] transition-all resize-y"
                    ></textarea>
                    {#if form.errors.notes}
                        <p class="text-xs font-medium text-red-600 dark:text-red-400 mt-1">{form.errors.notes}</p>
                    {/if}
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <Link href="/app/transactions">
                        <Button type="button" variant="outline">
                            {t('common.cancel')}
                        </Button>
                    </Link>
                    <Button
                        type="submit"
                        variant="primary"
                        loading={form.processing}
                    >
                        {isEditing ? t('transactions.update') : t('transactions.save')}
                    </Button>
                </div>
            </form>
        </Card>
    </div>

    <!-- Custom Delete Confirmation Dialog -->
    <ConfirmDialog
        bind:open={isDeleteConfirmOpen}
        title={`${t('actions.delete')} ${t('transactions.entity')}`}
        message={t('transactions.delete_confirm')}
        confirmText={`${t('actions.confirm')}, ${t('actions.delete')}`}
        cancelText={t('common.cancel')}
        variant="danger"
        loading={isDeleting}
        onconfirm={confirmDelete}
    />
</AppLayout>
