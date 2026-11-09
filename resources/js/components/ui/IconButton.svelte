<script lang="ts">
    import { LoaderCircle } from '@lucide/svelte';
    import { cn } from '@/lib/utils';
    import type { Snippet } from 'svelte';
    import type { HTMLButtonAttributes } from 'svelte/elements';

    interface Props extends HTMLButtonAttributes {
        label: string;
        variant?: 'primary' | 'secondary' | 'accent' | 'outline' | 'ghost' | 'destructive';
        size?: 'sm' | 'md' | 'lg';
        loading?: boolean;
        class?: string;
        children?: Snippet;
    }

    let {
        label,
        variant = 'ghost',
        size = 'md',
        loading = false,
        disabled = false,
        type = 'button',
        class: className = '',
        children,
        ...rest
    }: Props = $props();

    const variantStyles = {
        primary: 'bg-[#003A70] hover:bg-[#002B54] dark:bg-[#003A70] dark:hover:bg-[#0C3B67] text-white shadow-sm focus-visible:ring-[#003A70]/50',
        secondary: 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200',
        accent: 'bg-[#FFB700] hover:bg-[#FFD200] text-[#003A70] shadow-sm shadow-[#FFB700]/25 focus-visible:ring-[#FFB700]/50',
        outline: 'border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300',
        ghost: 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100',
        destructive: 'bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400',
    };

    const sizeStyles = {
        sm: 'w-8 h-8 rounded-md text-sm',
        md: 'w-10 h-10 rounded-lg text-base',
        lg: 'w-12 h-12 rounded-lg text-lg',
    };
</script>

<button
    {type}
    aria-label={label}
    title={label}
    disabled={disabled || loading}
    class={cn(
        'inline-flex items-center justify-center transition-all duration-150 cursor-pointer select-none',
        'focus-visible:outline-none focus-visible:ring-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95',
        variantStyles[variant],
        sizeStyles[size],
        className
    )}
    {...rest}
>
    {#if loading}
        <LoaderCircle class="animate-spin h-4 w-4 text-current" />
    {:else}
        {@render children?.()}
    {/if}
</button>
