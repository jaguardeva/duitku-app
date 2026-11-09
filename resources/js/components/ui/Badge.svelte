<script lang="ts">
    import { cn } from '@/lib/utils';
    import type { Snippet } from 'svelte';
    import type { HTMLAttributes } from 'svelte/elements';

    interface Props extends HTMLAttributes<HTMLSpanElement> {
        variant?: 'default' | 'primary' | 'accent' | 'success' | 'danger' | 'warning';
        size?: 'sm' | 'md';
        class?: string;
        children?: Snippet;
    }

    let {
        variant = 'default',
        size = 'md',
        class: className = '',
        children,
        ...rest
    }: Props = $props();

    const variantStyles = {
        default: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        primary: 'bg-[#003A70]/10 text-[#003A70] border border-[#003A70]/20 dark:bg-[#003A70]/40 dark:text-sky-300 dark:border-sky-700/40',
        accent: 'bg-[#FFB700]/15 text-[#8A6100] border border-[#FFB700]/40 dark:bg-[#FFB700]/20 dark:text-[#FFD200] dark:border-[#FFB700]/40 font-semibold',
        success: 'bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50',
        danger: 'bg-rose-50 text-rose-700 border border-rose-200/60 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50',
        warning: 'bg-orange-50 text-orange-700 border border-orange-200/60 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800/50',
    };

    const sizeStyles = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-1 text-xs font-medium',
    };
</script>

<span
    class={cn(
        'inline-flex items-center gap-1 rounded-md font-medium tracking-wide',
        variantStyles[variant],
        sizeStyles[size],
        className
    )}
    {...rest}
>
    {@render children?.()}
</span>
