<script lang="ts">
    import { cn } from '@/lib/utils';
    import type { Snippet } from 'svelte';
    import type { HTMLAttributes } from 'svelte/elements';

    interface Props extends HTMLAttributes<HTMLDivElement> {
        class?: string;
        children?: Snippet;
        hover?: boolean;
    }

    let {
        class: className = '',
        hover = false,
        children,
        ...rest
    }: Props = $props();
</script>

<div
    class={cn(
        'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl shadow-xs',
        hover ? 'transition-all duration-200 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700' : '',
        className
    )}
    {...rest}
>
    {@render children?.()}
</div>
