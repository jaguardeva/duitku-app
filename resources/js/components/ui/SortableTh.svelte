<script lang="ts">
    import { cn } from '@/lib/utils';
    import { ArrowDown, ArrowUp, ArrowUpDown } from '@lucide/svelte';
    import type { Snippet } from 'svelte';

    interface Props {
        field: string;
        currentSortBy?: string;
        currentSortDir?: 'asc' | 'desc';
        onsort: (field: string) => void;
        title?: string;
        class?: string;
        children?: Snippet;
    }

    let {
        field,
        currentSortBy,
        currentSortDir = 'asc',
        onsort,
        title = 'Klik untuk mengubah urutan data',
        class: className = '',
        children,
    }: Props = $props();

    const isActive = $derived(currentSortBy === field);
</script>

<th
    class={cn(
        'py-3 px-4 select-none cursor-pointer group transition-colors',
        isActive
            ? 'text-[#003A70] dark:text-[#FFB700] bg-slate-100/60 dark:bg-slate-800/80 font-bold'
            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100/40 dark:hover:bg-slate-800/30',
        className
    )}
    onclick={() => onsort(field)}
    {title}
>
    <div class="inline-flex items-center gap-1.5">
        {@render children?.()}
        {#if isActive}
            {#if currentSortDir === 'asc'}
                <ArrowUp class="w-3.5 h-3.5 shrink-0 text-[#003A70] dark:text-[#FFB700] transition-transform duration-150" />
            {:else}
                <ArrowDown class="w-3.5 h-3.5 shrink-0 text-[#003A70] dark:text-[#FFB700] transition-transform duration-150" />
            {/if}
        {:else}
            <ArrowUpDown class="w-3.5 h-3.5 shrink-0 opacity-0 group-hover:opacity-50 text-slate-400 dark:text-slate-500 transition-opacity duration-150" />
        {/if}
    </div>
</th>
