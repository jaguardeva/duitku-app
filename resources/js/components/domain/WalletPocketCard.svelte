<script lang="ts">
    import Badge from '@/components/ui/Badge.svelte';
    import { balanceVisibility } from '@/lib/balanceVisibility.svelte';
    import { t } from '@/lib/i18n';
    import {
        Archive,
        Banknote,
        Building2,
        CreditCard,
        MoreVertical,
        PiggyBank,
        Wallet as WalletIcon,
    } from '@lucide/svelte';

    interface WalletData {
        id: string;
        name: string;
        type: string;
        currency_code: string;
        current_balance?: string | number;
        opening_balance: string | number;
        icon?: string;
        color_token?: string;
        is_archived: boolean;
    }

    interface Props {
        wallet: WalletData;
        onclick?: () => void;
        onedit?: (wallet: WalletData) => void;
        onarchive?: (wallet: WalletData) => void;
    }

    let {
        wallet,
        onclick,
        onedit,
        onarchive,
    }: Props = $props();

    let isMenuOpen = $state(false);

    const typeIcons = {
        cash: Banknote,
        bank: Building2,
        e_wallet: CreditCard,
        savings: PiggyBank,
        other: WalletIcon,
    };

    const typeNames: Record<string, string> = {
        cash: t('domain.wallet_types.cash'),
        bank: t('domain.wallet_types.bank'),
        e_wallet: t('domain.wallet_types.e_wallet'),
        savings: t('domain.wallet_types.savings'),
        other: t('domain.wallet_types.other'),
    };

    const IconComponent = $derived(
        typeIcons[wallet.type as keyof typeof typeIcons] || WalletIcon
    );

    const balance = $derived(
        wallet.current_balance !== undefined ? wallet.current_balance : wallet.opening_balance
    );
</script>

<div
    role="button"
    tabindex="0"
    {onclick}
    onkeydown={(e) => e.key === 'Enter' && onclick?.()}
    class={`relative rounded-2xl p-4 sm:p-5 border transition-all duration-200 select-none ${
        onclick ? 'cursor-pointer active:scale-[0.99]' : ''
    } ${
        wallet.is_archived
            ? 'bg-slate-100/60 dark:bg-slate-900/40 border-slate-200 dark:border-slate-800 opacity-75'
            : 'bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800 hover:border-[#003A70] dark:hover:border-[#FFB700] hover:shadow-md'
    }`}
>
    <!-- Top Row: Icon + Type + Action menu -->
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-[#003A70]/10 dark:bg-[#0C3B67]/60 text-[#003A70] dark:text-[#FFD200] flex items-center justify-center shrink-0 border border-[#003A70]/20 dark:border-[#FFB700]/30 shadow-2xs">
                <IconComponent class="w-5 h-5" />
            </div>
            <div class="min-w-0">
                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate">
                    {wallet.name}
                </h4>
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 truncate">
                    {typeNames[wallet.type] || wallet.type}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-1.5 shrink-0">
            <Badge variant="default" size="sm" class="font-mono uppercase text-[10px] font-bold">
                {wallet.currency_code}
            </Badge>

            {#if onedit || onarchive}
                <!-- Card Context Options Menu -->
                <div class="relative">
                    <button
                        type="button"
                        onclick={(e) => {
                            e.stopPropagation();
                            isMenuOpen = !isMenuOpen;
                        }}
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        aria-label={t('domain.wallet')}
                    >
                        <MoreVertical class="w-4 h-4" />
                    </button>

                    {#if isMenuOpen}
                        <button
                            type="button"
                            class="fixed inset-0 w-full h-full cursor-default bg-transparent z-40"
                            onclick={(e) => {
                                e.stopPropagation();
                                isMenuOpen = false;
                            }}
                            tabindex="-1"
                            aria-label={t('common.close')}
                        ></button>

                        <div class="absolute right-0 mt-1 w-36 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-md py-1 z-50 text-xs animate-in zoom-in-95 duration-100">
                            {#if onedit}
                                <button
                                    type="button"
                                    onclick={(e) => {
                                        e.stopPropagation();
                                        isMenuOpen = false;
                                        onedit?.(wallet);
                                    }}
                                    class="w-full text-left px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 cursor-pointer"
                                >
                                    {t('actions.edit')} {t('domain.wallet')}
                                </button>
                            {/if}
                            {#if onarchive}
                                <button
                                    type="button"
                                    onclick={(e) => {
                                        e.stopPropagation();
                                        isMenuOpen = false;
                                        onarchive?.(wallet);
                                    }}
                                    class="w-full text-left px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 cursor-pointer flex items-center gap-1.5"
                                >
                                    <Archive class="w-3.5 h-3.5" />
                                    {wallet.is_archived ? t('actions.restore') : t('actions.archive')}
                                </button>
                            {/if}
                        </div>
                    {/if}
                </div>
            {/if}
        </div>
    </div>

    <!-- Balance Row -->
    <div class="mt-2">
        <p class="text-[11px] font-semibold tracking-wider uppercase text-slate-400 dark:text-slate-500 mb-0.5">
            {t('domain.current_balance')}
        </p>
        <div class="text-xl font-bold font-mono tracking-tight text-slate-900 dark:text-slate-100 truncate">
            {balanceVisibility.format(balance, wallet.currency_code)}
        </div>
    </div>

    {#if wallet.is_archived}
        <div class="mt-3 pt-2 border-t border-slate-200/60 dark:border-slate-800 flex items-center gap-1 text-[11px] font-medium text-amber-600 dark:text-amber-400">
            <Archive class="w-3.5 h-3.5" />
            <span>{t('domain.archived_wallet')}</span>
        </div>
    {/if}
</div>
