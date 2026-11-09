<script lang="ts">
    import { t } from '@/lib/i18n';
    import { formatCurrency, formatTransactionDateTime } from '@/lib/utils';
    import {
        ArrowDownLeft,
        ArrowUpRight,
        Briefcase,
        Calendar,
        Car,
        CircleEllipsis,
        Clock,
        Film,
        Gift,
        GraduationCap,
        HeartPulse,
        Home,
        Receipt,
        ShoppingBag,
        Sparkles,
        Tag,
        TrendingUp,
        Utensils,
        Wallet,
    } from '@lucide/svelte';

    interface TransactionData {
        id: string;
        type: 'income' | 'expense';
        title: string;
        amount: string | number;
        currency_code: string;
        transaction_date: string;
        transaction_time?: string | null;
        notes?: string | null;
        wallet?: { name: string; currency_code?: string };
        category?: { name: string; icon?: string; color_token?: string };
    }

    interface Props {
        transaction: TransactionData;
        onclick?: () => void;
    }

    let { transaction, onclick }: Props = $props();

    const iconMap: Record<string, any> = {
        utensils: Utensils,
        car: Car,
        'shopping-bag': ShoppingBag,
        receipt: Receipt,
        'heart-pulse': HeartPulse,
        film: Film,
        'graduation-cap': GraduationCap,
        home: Home,
        'circle-ellipsis': CircleEllipsis,
        wallet: Wallet,
        gift: Gift,
        briefcase: Briefcase,
        sparkles: Sparkles,
        'trending-up': TrendingUp,
    };

    const CategoryIcon = $derived(
        (transaction.category?.icon && iconMap[transaction.category.icon]) ||
        (transaction.type === 'income' ? ArrowDownLeft : ArrowUpRight)
    );

    const dateTime = $derived(
        formatTransactionDateTime(transaction.transaction_date, transaction.transaction_time)
    );
</script>

<div
    role="button"
    tabindex="0"
    {onclick}
    onkeydown={(e) => e.key === 'Enter' && onclick?.()}
    class="flex items-start sm:items-center justify-between p-3 sm:p-4 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors cursor-pointer border border-transparent hover:border-slate-200/80 dark:hover:border-slate-800/80 select-none group gap-3"
>
    <!-- Left: Category Icon & Details -->
    <div class="flex items-start sm:items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
        <div
            class={`w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center shrink-0 border transition-transform duration-200 group-hover:scale-105 shadow-2xs mt-0.5 sm:mt-0 ${
                transaction.type === 'income'
                    ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-900/60'
                    : 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border-rose-200/60 dark:border-rose-900/60'
            }`}
        >
            <CategoryIcon class="w-5 h-5" />
        </div>

        <div class="min-w-0 flex-1">
            <!-- Title -->
            <h5
                class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate group-hover:text-[#003A70] dark:group-hover:text-[#FFD200] transition-colors"
                title={transaction.title}
            >
                {transaction.title}
            </h5>

            <!-- Optional Notes Preview -->
            {#if transaction.notes}
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 italic truncate max-w-xs sm:max-w-md mt-0.5">
                    "{transaction.notes}"
                </p>
            {/if}

            <!-- Metadata Row 1: Tanggal & Jam -->
            <div class="flex items-center gap-1.5 text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">
                <span class="inline-flex items-center gap-1 font-medium text-slate-600 dark:text-slate-300 shrink-0">
                    <Calendar class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" />
                    <span>{dateTime.dateLabel}</span>
                    {#if dateTime.timeLabel}
                        <span class="text-slate-300 dark:text-slate-600">•</span>
                        <span class="inline-flex items-center gap-0.5">
                            <Clock class="w-2.5 h-2.5 text-slate-400 dark:text-slate-500 shrink-0" />
                            <span>{dateTime.timeLabel}</span>
                        </span>
                    {/if}
                </span>
            </div>

            <!-- Metadata Row 2: Badge Kategori & Badge Dompet -->
            {#if transaction.category || transaction.wallet}
                <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                    {#if transaction.category}
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-medium truncate max-w-[130px] sm:max-w-none">
                            <Tag class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" />
                            <span class="truncate">{transaction.category.name}</span>
                        </span>
                    {/if}

                    {#if transaction.wallet}
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-medium truncate max-w-[120px] sm:max-w-none">
                            <Wallet class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" />
                            <span class="truncate">{transaction.wallet.name}</span>
                        </span>
                    {/if}
                </div>
            {/if}
        </div>
    </div>

    <!-- Right: Amount & Type Indicator -->
    <div class="text-right shrink-0 flex flex-col items-end justify-center self-start sm:self-center ml-2.5 sm:ml-4">
        <div
            class={`text-sm sm:text-base font-extrabold font-mono tracking-tight whitespace-nowrap ${
                transaction.type === 'income'
                    ? 'text-emerald-600 dark:text-emerald-400'
                    : 'text-rose-600 dark:text-rose-400'
            }`}
        >
            {transaction.type === 'income' ? '+' : '-'} {formatCurrency(transaction.amount, transaction.currency_code)}
        </div>

        <span
            class={`text-[10px] font-semibold uppercase tracking-wider mt-0.5 ${
                transaction.type === 'income'
                    ? 'text-emerald-600/80 dark:text-emerald-400/80'
                    : 'text-rose-600/80 dark:text-rose-400/80'
            }`}
        >
            {transaction.type === 'income' ? t('domain.income') : t('domain.expense')}
        </span>
    </div>
</div>
