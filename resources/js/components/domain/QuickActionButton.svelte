<script lang="ts">
    import { cn } from '@/lib/utils';
    import { Link } from '@inertiajs/svelte';
    import type { Component } from 'svelte';

    interface Props {
        label: string;
        icon: Component<{ class?: string }>;
        variant?: 'income' | 'expense' | 'transfer' | 'wallet' | 'budget' | 'default';
        onclick?: () => void;
        href?: string;
        class?: string;
    }

    let {
        label,
        icon: Icon,
        variant = 'default',
        onclick,
        href,
        class: className = '',
    }: Props = $props();

    const variantStyles = {
        default: 'bg-[#003A70]/10 text-[#003A70] border-[#003A70]/20 group-hover:bg-[#003A70] group-hover:text-white dark:bg-[#003A70]/40 dark:text-sky-300 dark:border-sky-800/40 dark:group-hover:bg-[#003A70] dark:group-hover:text-white',
        income: 'bg-emerald-50 text-emerald-600 border-emerald-200/70 group-hover:bg-emerald-600 group-hover:text-white dark:bg-emerald-950/60 dark:text-emerald-400 dark:border-emerald-900/40 dark:group-hover:bg-emerald-500 dark:group-hover:text-white',
        expense: 'bg-rose-50 text-rose-600 border-rose-200/70 group-hover:bg-rose-600 group-hover:text-white dark:bg-rose-950/60 dark:text-rose-400 dark:border-rose-900/40 dark:group-hover:bg-rose-500 dark:group-hover:text-white',
        transfer: 'bg-amber-50 text-amber-600 border-amber-200/70 group-hover:bg-amber-500 group-hover:text-white dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900/40 dark:group-hover:bg-amber-400 dark:group-hover:text-[#003A70]',
        wallet: 'bg-[#003A70]/10 text-[#003A70] border-[#003A70]/20 group-hover:bg-[#003A70] group-hover:text-white dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800/40 dark:group-hover:bg-sky-600 dark:group-hover:text-white',
        budget: 'bg-indigo-50 text-indigo-600 border-indigo-200/70 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-900/40 dark:group-hover:bg-indigo-500 dark:group-hover:text-white',
    };
</script>

{#if href}
    <Link
        {href}
        class={cn(
            'flex min-w-0 flex-col items-center gap-2 group cursor-pointer text-center select-none rounded-xl px-1 py-2 transition-colors hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#003A70] dark:hover:bg-slate-800/70 dark:focus-visible:ring-[#FFB700]',
            className
        )}
        aria-label={label}
    >
        <div
            class={cn(
                'w-11 h-11 rounded-lg flex items-center justify-center border transition-all duration-200 group-hover:scale-105 group-hover:shadow-sm active:scale-95',
                variantStyles[variant]
            )}
        >
            <Icon class="w-5 h-5 transition-transform duration-200" />
        </div>
        <span class="w-full max-w-[5.5rem] text-xs font-semibold leading-tight text-center text-slate-700 whitespace-normal break-normal transition-colors dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white">
            {label}
        </span>
    </Link>
{:else}
    <button
        type="button"
        {onclick}
        class={cn(
            'flex min-w-0 flex-col items-center gap-2 group cursor-pointer text-center select-none rounded-xl px-1 py-2 transition-colors hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#003A70] dark:hover:bg-slate-800/70 dark:focus-visible:ring-[#FFB700]',
            className
        )}
        aria-label={label}
    >
        <div
            class={cn(
                'w-11 h-11 rounded-lg flex items-center justify-center border transition-all duration-200 group-hover:scale-105 group-hover:shadow-sm active:scale-95',
                variantStyles[variant]
            )}
        >
            <Icon class="w-5 h-5 transition-transform duration-200" />
        </div>
        <span class="w-full max-w-[5.5rem] text-xs font-semibold leading-tight text-center text-slate-700 whitespace-normal break-normal transition-colors dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white">
            {label}
        </span>
    </button>
{/if}
