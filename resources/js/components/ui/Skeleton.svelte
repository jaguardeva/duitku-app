<script lang="ts">
    import { cn } from '@/lib/utils';
    import type { HTMLAttributes } from 'svelte/elements';

    interface Props extends HTMLAttributes<HTMLDivElement> {
        class?: string;
    }

    let {
        class: className = '',
        ...rest
    }: Props = $props();
</script>

<div
    class={cn(
        'animate-pulse rounded-md bg-slate-200/80 dark:bg-slate-800',
        className
    )}
    {...rest}
></div>
