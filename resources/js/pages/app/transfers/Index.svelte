<script lang="ts">
    import Button from '@/components/ui/Button.svelte';
    import Card from '@/components/ui/Card.svelte';
    import ConfirmDialog from '@/components/ui/ConfirmDialog.svelte';
    import CurrencyInput from '@/components/ui/CurrencyInput.svelte';
    import DatePicker from '@/components/ui/DatePicker.svelte';
    import EmptyState from '@/components/ui/EmptyState.svelte';
    import Input from '@/components/ui/Input.svelte';
    import Modal from '@/components/ui/Modal.svelte';
    import Select from '@/components/ui/Select.svelte';
    import TimePicker from '@/components/ui/TimePicker.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { t } from '@/lib/i18n';
    import { formatCurrency, formatDate, formatTransactionDateTime } from '@/lib/utils';
    import { Link, router, useForm } from '@inertiajs/svelte';
    import {
        ArrowLeftRight,
        ArrowRight,
        Calendar,
        Plus,
        Trash2,
        Wallet,
    } from '@lucide/svelte';

    interface WalletItem {
        id: string;
        name: string;
        currency_code: string;
        type?: string;
        current_balance: string | number;
    }

    interface TransferItem {
        id: string;
        amount: string | number;
        currency_code: string;
        transfer_date: string;
        transfer_time?: string;
        note?: string;
        source_wallet?: { id: string; name: string };
        destination_wallet?: { id: string; name: string };
    }

    interface Props {
        transfers?: {
            data: TransferItem[];
            current_page: number;
            last_page: number;
            prev_page_url?: string;
            next_page_url?: string;
            total: number;
        };
        wallets?: WalletItem[];
    }

    let {
        transfers = { data: [], current_page: 1, last_page: 1, total: 0 },
        wallets = [],
    }: Props = $props();

    let isModalOpen = $state(false);
    const today = new Date().toISOString().split('T')[0];

    const form = useForm({
        source_wallet_id: '',
        destination_wallet_id: '',
        amount: '',
        transfer_date: today,
        transfer_time: '',
        note: '',
    });

    // When source wallet is chosen, get its currency
    const selectedSourceWallet = $derived(
        wallets.find((w) => w.id === form.source_wallet_id)
    );

    const enteredAmount = $derived(Number(form.amount || 0));
    const insufficientBalanceError = $derived.by(() => {
        if (!selectedSourceWallet || !form.amount || enteredAmount <= 0) {
            return '';
        }

        return enteredAmount > Number(selectedSourceWallet.current_balance)
            ? t('transfers.insufficient_balance')
            : '';
    });
    const amountError = $derived(insufficientBalanceError || form.errors.amount);

    // Eligible destination wallets: must belong to user and not equal source wallet
    const destinationWalletOptions = $derived([
        { value: '', label: `-- ${t('transfers.destination')} --` },
        ...wallets
            .filter((w) => w.id !== form.source_wallet_id)
            .map((w) => ({ value: w.id, label: w.name })),
    ]);

    const sourceWalletOptions = $derived([
        { value: '', label: `-- ${t('transfers.source')} --` },
        ...wallets.map((w) => ({ value: w.id, label: w.name })),
    ]);

    function openModal() {
        form.reset();
        form.source_wallet_id = wallets[0]?.id || '';
        isModalOpen = true;
    }

    function handleSubmit(e: SubmitEvent) {
        e.preventDefault();
        form.post('/app/transfers', {
            onSuccess: () => {
                isModalOpen = false;
                form.reset();
            },
        });
    }

    function clearAmountServerError() {
        form.clearErrors('amount');
    }

    let transferToDelete = $state<TransferItem | null>(null);
    let isDeletingTransfer = $state(false);

    function requestDelete(transfer: TransferItem) {
        transferToDelete = transfer;
    }

    function confirmDeleteTransfer() {
        if (!transferToDelete) return;
        isDeletingTransfer = true;
        router.delete(`/app/transfers/${transferToDelete.id}`, {
            onFinish: () => {
                isDeletingTransfer = false;
                transferToDelete = null;
            },
        });
    }
</script>

<AppLayout title={t('transfers.title')}>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                    Transfer Antar-Dompet
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Pindahkan saldo antar kantung Anda tanpa memengaruhi arus kas pemasukan/pengeluaran.
                </p>
            </div>
            <Button
                variant="primary"
                onclick={openModal}
                disabled={wallets.length < 2}
                class="text-xs font-semibold"
            >
                <Plus class="w-4 h-4" />
                Transfer Baru
            </Button>
        </div>

        {#if wallets.length < 2}
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-center justify-between">
                <span>{t('transfers.minimum_wallets')}</span>
                <Link href="/app/wallets" class="font-bold underline ml-2">{t('actions.add')} {t('domain.wallet')}</Link>
            </div>
        {/if}

        <!-- Transfer List -->
        <Card class="p-2 divide-y divide-slate-100 dark:divide-slate-800">
            {#each transfers.data as item (item.id)}
                {@const dt = formatTransactionDateTime(item.transfer_date, item.transfer_time)}
                <div class="flex items-start sm:items-center justify-between p-3.5 sm:p-4 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors rounded-xl gap-3">
                    <div class="flex items-start sm:items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-200/60 dark:border-amber-900/60 shadow-2xs mt-0.5 sm:mt-0">
                            <ArrowLeftRight class="w-5 h-5" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 sm:gap-2 text-sm font-bold text-slate-900 dark:text-slate-100 flex-wrap">
                                <span class="truncate max-w-[130px] sm:max-w-none">{item.source_wallet?.name || t('transfers.source_fallback')}</span>
                                <ArrowRight class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                <span class="truncate max-w-[130px] sm:max-w-none text-[#003A70] dark:text-[#FFD200]">{item.destination_wallet?.name || t('transfers.destination_fallback')}</span>
                            </div>

                            {#if item.note}
                                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 italic truncate max-w-xs sm:max-w-md mt-0.5">
                                    "{item.note}"
                                </p>
                            {/if}

                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">
                                <span class="inline-flex items-center gap-1 font-medium text-slate-600 dark:text-slate-300 shrink-0">
                                    <Calendar class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" />
                                    <span>{dt.dateLabel}</span>
                                    {#if dt.timeLabel}
                                        <span class="text-slate-400 dark:text-slate-500">•</span>
                                        <span>{dt.timeLabel}</span>
                                    {/if}
                                </span>
                                <span class="hidden sm:inline text-slate-300 dark:text-slate-700">•</span>
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-[11px] font-medium border border-amber-200/50 dark:border-amber-900/40">
                                    Transfer Antar Dompet
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-4 shrink-0 ml-2.5 sm:ml-4 self-start sm:self-center">
                        <div class="text-right">
                            <span class="text-sm sm:text-base font-extrabold font-mono text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                {formatCurrency(item.amount, item.currency_code)}
                            </span>
                        </div>

                        <button
                            type="button"
                            onclick={() => requestDelete(item)}
                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                        title={t('actions.delete')}
                        aria-label={t('actions.delete')}
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            {:else}
                <div class="py-12">
                    <EmptyState
        title={t('transfers.empty')}
                        description={t('transfers.description')}
                    >
                        {#snippet action()}
                            {#if wallets.length >= 2}
                                <Button variant="primary" size="sm" onclick={openModal}>
                                    <Plus class="w-4 h-4" />
                                    {t('transfers.action')}
                                </Button>
                            {/if}
                        {/snippet}
                    </EmptyState>
                </div>
            {/each}
        </Card>
    </div>

    <!-- New Transfer Modal -->
    <Modal
        bind:open={isModalOpen}
        title={t('transfers.title')}
        size="md"
    >
        <form onsubmit={handleSubmit} class="space-y-4">
            <Select
                label={t('transfers.source')}
                options={sourceWalletOptions}
                required
                bind:value={form.source_wallet_id}
                error={form.errors.source_wallet_id}
                onchange={clearAmountServerError}
            />

            <Select
                label={t('transfers.destination')}
                options={destinationWalletOptions}
                required
                bind:value={form.destination_wallet_id}
                error={form.errors.destination_wallet_id}
            />

            <CurrencyInput
                id="transfer-amount"
                label={t('transfers.amount')}
                placeholder="0"
                required
                bind:value={form.amount}
                error={amountError}
                onchange={clearAmountServerError}
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <DatePicker
                    label={t('transfers.date')}
                    required
                    bind:value={form.transfer_date}
                    error={form.errors.transfer_date}
                />

                <TimePicker
                    label={t('transfers.time')}
                    bind:value={form.transfer_time}
                    error={form.errors.transfer_time}
                    align="right"
                />
            </div>

            <Input
                label={t('transfers.notes')}
                placeholder={t('transfers.notes_placeholder')}
                maxlength={500}
                bind:value={form.note}
                error={form.errors.note}
            />

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                <Button
                    type="button"
                    variant="outline"
                    onclick={() => (isModalOpen = false)}
                >
                    {t('common.cancel')}
                </Button>
                <Button
                    type="submit"
                    variant="primary"
                    loading={form.processing}
                    disabled={Boolean(insufficientBalanceError)}
                >
                    {t('transfers.action')}
                </Button>
            </div>
        </form>
    </Modal>

    <!-- Custom Cancel/Delete Transfer Confirmation Dialog -->
    <ConfirmDialog
        open={!!transferToDelete}
        title={t('transfers.cancel')}
        message={t('transfers.confirm_cancel')}
        confirmText={t('transfers.confirm_cancel')}
        cancelText="Kembali"
        variant="danger"
        loading={isDeletingTransfer}
        onconfirm={confirmDeleteTransfer}
        oncancel={() => (transferToDelete = null)}
    />
</AppLayout>
