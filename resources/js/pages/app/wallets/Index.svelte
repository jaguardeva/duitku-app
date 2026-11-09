<script lang="ts">
    import WalletPocketCard from '@/components/domain/WalletPocketCard.svelte';
    import Button from '@/components/ui/Button.svelte';
    import CurrencyInput from '@/components/ui/CurrencyInput.svelte';
    import EmptyState from '@/components/ui/EmptyState.svelte';
    import Input from '@/components/ui/Input.svelte';
    import Modal from '@/components/ui/Modal.svelte';
    import Select from '@/components/ui/Select.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { router, useForm } from '@inertiajs/svelte';
    import { t } from '@/lib/i18n';
    import { Archive, Plus, Wallet } from '@lucide/svelte';

    interface WalletItem {
        id: string;
        name: string;
        type: string;
        currency_code: string;
        opening_balance: string | number;
        current_balance?: string | number;
        icon?: string;
        color_token?: string;
        is_archived: boolean;
    }

    interface Props {
        wallets?: WalletItem[];
        balancesByCurrency?: Record<string, string>;
    }

    let {
        wallets = [],
        balancesByCurrency = {},
    }: Props = $props();

    let isCreateModalOpen = $state(false);
    let editingWallet = $state<WalletItem | null>(null);
    let activeTab = $state<'active' | 'archived'>('active');

    const form = useForm({
        name: '',
        type: 'bank',
        currency_code: 'IDR',
        opening_balance: 0,
        icon: 'wallet',
        color_token: 'blue',
    });

    const editForm = useForm({
        name: '',
        type: 'bank',
        icon: 'wallet',
        color_token: 'blue',
    });

    const activeWallets = $derived(wallets.filter((w) => !w.is_archived));
    const archivedWallets = $derived(wallets.filter((w) => w.is_archived));
    const displayedWallets = $derived(activeTab === 'active' ? activeWallets : archivedWallets);

    function openCreateModal() {
        form.reset();
        isCreateModalOpen = true;
    }

    function handleCreateSubmit(e: SubmitEvent) {
        e.preventDefault();
        form.post('/app/wallets', {
            onSuccess: () => {
                isCreateModalOpen = false;
                form.reset();
            },
        });
    }

    function handleEdit(wallet: WalletItem) {
        editingWallet = wallet;
        editForm.name = wallet.name;
        editForm.type = wallet.type;
        editForm.icon = wallet.icon || 'wallet';
        editForm.color_token = wallet.color_token || 'blue';
    }

    function handleEditSubmit(e: SubmitEvent) {
        e.preventDefault();
        if (!editingWallet) return;
        editForm.put(`/app/wallets/${editingWallet.id}`, {
            onSuccess: () => {
                editingWallet = null;
            },
        });
    }

    function handleToggleArchive(wallet: WalletItem) {
        router.post(`/app/wallets/${wallet.id}/archive`);
    }

    const typeOptions = [
        { value: 'cash', label: t('domain.wallet_types.cash') },
        { value: 'bank', label: t('domain.wallet_types.bank') },
        { value: 'e_wallet', label: t('domain.wallet_types.e_wallet') },
        { value: 'savings', label: t('domain.wallet_types.savings') },
        { value: 'other', label: t('domain.wallet_types.other') },
    ];
</script>

<AppLayout title={t('wallets.title')}>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                {t('wallets.heading')}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {t('wallets.description')}
                </p>
            </div>
            <Button variant="primary" onclick={openCreateModal} class="text-xs font-semibold">
                <Plus class="w-4 h-4" />
                {t('wallets.create')}
            </Button>
        </div>

        <!-- Tabs: Active vs Archived -->
        <div class="flex items-center gap-2 border-b border-slate-200/80 dark:border-slate-800 text-xs">
            <button
                type="button"
                onclick={() => (activeTab = 'active')}
                class={`px-4 py-2.5 font-semibold transition-colors border-b-2 cursor-pointer ${
                    activeTab === 'active'
                        ? 'border-[#003A70] text-[#003A70] dark:border-[#FFB700] dark:text-[#FFB700]'
                        : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                }`}
            >
                {t('wallets.active')} ({activeWallets.length})
            </button>
            <button
                type="button"
                onclick={() => (activeTab = 'archived')}
                class={`px-4 py-2.5 font-semibold transition-colors border-b-2 cursor-pointer flex items-center gap-1.5 ${
                    activeTab === 'archived'
                        ? 'border-[#003A70] text-[#003A70] dark:border-[#FFB700] dark:text-[#FFB700]'
                        : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                }`}
            >
                <Archive class="w-3.5 h-3.5" />
                {t('wallets.archived')} ({archivedWallets.length})
            </button>
        </div>

        <!-- Wallets Grid -->
        {#if displayedWallets.length === 0}
            <EmptyState
                title={activeTab === 'active' ? t('wallets.no_active') : t('wallets.no_archived')}
                description={t('wallets.create_hint')}
            >
                {#snippet action()}
                    {#if activeTab === 'active'}
                        <Button variant="primary" size="sm" onclick={openCreateModal}>
                            <Plus class="w-4 h-4" />
                            {t('actions.create')} {t('domain.wallet')}
                        </Button>
                    {/if}
                {/snippet}
            </EmptyState>
        {:else}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {#each displayedWallets as wallet (wallet.id)}
                    <WalletPocketCard
                        {wallet}
                        onedit={handleEdit}
                        onarchive={handleToggleArchive}
                    />
                {/each}
            </div>
        {/if}
    </div>

    <!-- Create Wallet Modal -->
    <Modal
        bind:open={isCreateModalOpen}
        title={t('wallets.create')}
        size="md"
    >
        <form onsubmit={handleCreateSubmit} class="space-y-4">
            <Input
                label={t('wallets.name')}
                placeholder={t('wallets.name_placeholder')}
                required
                bind:value={form.name}
                error={form.errors.name}
            />

            <Select
                label={t('wallets.type')}
                options={typeOptions}
                required
                bind:value={form.type}
                error={form.errors.type}
            />

            <CurrencyInput
                label={t('wallets.opening_balance')}
                placeholder="0"
                required
                bind:value={form.opening_balance}
                error={form.errors.opening_balance}
                hint={t('wallets.opening_balance_hint')}
            />

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
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

    <!-- Edit Wallet Modal -->
    <Modal
        open={editingWallet !== null}
        onclose={() => (editingWallet = null)}
        title={`${t('actions.edit')} ${t('domain.wallet')}`}
        size="md"
    >
        <form onsubmit={handleEditSubmit} class="space-y-4">
            <Input
                label={t('wallets.name')}
                required
                bind:value={editForm.name}
                error={editForm.errors.name}
            />

            <Select
                label={t('wallets.type')}
                options={typeOptions}
                required
                bind:value={editForm.type}
                error={editForm.errors.type}
            />

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <Button
                    type="button"
                    variant="outline"
                    onclick={() => (editingWallet = null)}
                >
                    {t('common.cancel')}
                </Button>
                <Button
                    type="submit"
                    variant="primary"
                    loading={editForm.processing}
                >
                    {t('actions.update')} {t('domain.wallet')}
                </Button>
            </div>
        </form>
    </Modal>
</AppLayout>
