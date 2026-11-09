<script lang="ts">
    import { LoaderCircle } from '@lucide/svelte';
    import { cn } from '@/lib/utils';
    import type { Snippet } from 'svelte';
    import type { HTMLButtonAttributes } from 'svelte/elements';

    interface Props extends HTMLButtonAttributes {
        variant?: 'primary' | 'secondary' | 'accent' | 'outline' | 'ghost' | 'destructive';
        size?: 'sm' | 'md' | 'lg';
        loading?: boolean;
        class?: string;
        children?: Snippet;
    }

    let {
        variant = 'primary',
        size = 'md',
        loading = false,
        disabled = false,
        type = 'button',
        class: className = '',
        children,
        ...rest
    }: Props = $props();

    const variantStyles = {
        primary: 'bg-[#003A70] hover:bg-[#002B54] dark:bg-[#003A70] dark:hover:bg-[#0C3B67] text-white shadow-sm focus-visible:ring-[#003A70]/50 border border-transparent dark:border-[#1E3A5F]',
        secondary: 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100',
        accent: 'bg-[#FFB700] hover:bg-[#FFD200] text-[#003A70] font-bold shadow-sm shadow-[#FFB700]/25 focus-visible:ring-[#FFB700]/50',
        outline: 'border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200',
        ghost: 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300',
        destructive: 'bg-red-600 hover:bg-red-700 text-white shadow-sm focus-visible:ring-red-500/50',
    };

    const sizeStyles = {
        sm: 'h-8 px-3 text-xs gap-1.5',
        md: 'h-10 px-4 text-sm gap-2',
        lg: 'h-12 px-6 text-base gap-2.5',
    };
</script>

<button
    {type}
    disabled={disabled || loading}
    class={cn(
        'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-150 select-none cursor-pointer',
        'focus-visible:outline-none focus-visible:ring-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none active:scale-[0.98]',
        variantStyles[variant],
        sizeStyles[size],
        className
    )}
    {...rest}
>
    {#if loading}
        <LoaderCircle class="animate-spin -ml-0.5 h-4 w-4 text-current" />
    {/if}
    {@render children?.()}
</button>
