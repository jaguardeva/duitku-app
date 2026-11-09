<script lang="ts">
    import { cn } from '@/lib/utils';
    import { t } from '@/lib/i18n';
    import { Link, router } from '@inertiajs/svelte';
    import { ChevronLeft, ChevronRight } from '@lucide/svelte';

    export interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    export interface PaginationData {
        current_page: number;
        last_page: number;
        per_page: number;
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url?: string | null;
        next_page_url?: string | null;
        path?: string;
        links?: PaginationLink[];
    }

    interface Props {
        pagination: PaginationData;
        variant?: 'full' | 'top' | 'bottom';
        perPageOptions?: number[];
        showPerPage?: boolean;
        showInfo?: boolean;
        itemName?: string;
        class?: string;
    }

    let {
        pagination,
        variant = 'full',
        perPageOptions = [10, 25, 50, 100],
        showPerPage = true,
        showInfo = true,
        itemName = t('pagination.results'),
        class: className = '',
    }: Props = $props();

    function handlePerPageChange(e: Event) {
        const select = e.target as HTMLSelectElement;
        const newPerPage = select.value;
        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);
        searchParams.set('per_page', newPerPage);
        searchParams.set('page', '1');

        const targetUrl = `${currentUrl.pathname}?${searchParams.toString()}`;
        router.get(targetUrl, {}, { preserveState: true, preserveScroll: true });
    }

    // Filter out previous & next links from the links array to isolate numbered page links
    const pageNumberLinks = $derived(
        (pagination.links || []).filter((_, index, array) => index > 0 && index < array.length - 1)
    );
</script>

<div
    class={cn(
        'text-xs select-none',
        variant === 'full' && 'flex flex-col sm:flex-row items-center justify-between gap-4 py-3 px-1',
        variant === 'top' && 'flex flex-col sm:flex-row items-center justify-between gap-3 py-2 px-1',
        variant === 'bottom' && 'flex items-center justify-between sm:justify-end gap-3 py-3 px-1',
        className
    )}
>
    <!-- Left Info & Per Page Toolbar (rendered in 'full' or 'top') -->
    {#if variant === 'full' || variant === 'top'}
        <div class="w-full sm:w-auto flex flex-wrap items-center justify-between sm:justify-start gap-3">
            {#if showPerPage}
                <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                    <span>{t('pagination.per_page')}</span>
                    <select
                        value={pagination.per_page}
                        onchange={handlePerPageChange}
                        class="h-8 px-2 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:border-slate-300 dark:hover:border-slate-600 focus:outline-none focus:ring-1 focus:ring-[#003A70] dark:focus:ring-[#FFB700] cursor-pointer"
                    >
                        {#each perPageOptions as option}
                            <option value={option}>{option}</option>
                        {/each}
                    </select>
                </div>
            {/if}

            {#if showInfo && pagination.total > 0}
                <span class="text-slate-500 dark:text-slate-400">
                    {t('pagination.showing')}
                    <span class="font-bold text-slate-700 dark:text-slate-200">{pagination.from ?? 0}</span>
                    {t('pagination.to')}
                    <span class="font-bold text-slate-700 dark:text-slate-200">{pagination.to ?? 0}</span>
                    {t('pagination.of')}
                    <span class="font-bold text-slate-700 dark:text-slate-200">{pagination.total}</span>
                    {itemName}
                </span>
            {:else if showInfo}
                <span class="text-slate-500 dark:text-slate-400">
                    0 {itemName}
                </span>
            {/if}
        </div>
    {/if}

    <!-- Right Navigation Buttons (rendered in 'full' or 'bottom') -->
    {#if (variant === 'full' || variant === 'bottom') && pagination.last_page > 1}
        <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end gap-1.5">
            <!-- Previous Button -->
            {#if pagination.prev_page_url}
                <Link
                    href={pagination.prev_page_url}
                    preserveState
                    preserveScroll
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-colors shadow-2xs"
                >
                    <ChevronLeft class="w-3.5 h-3.5" />
                    <span>{t('pagination.previous')}</span>
                </Link>
            {:else}
                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-100 dark:border-slate-800/80 bg-slate-50/60 dark:bg-slate-900/40 text-slate-400 dark:text-slate-600 cursor-not-allowed">
                    <ChevronLeft class="w-3.5 h-3.5" />
                    <span>{t('pagination.previous')}</span>
                </span>
            {/if}

            <!-- Numbered Links (Desktop) -->
            {#if pageNumberLinks.length > 0}
                <div class="hidden sm:inline-flex items-center gap-1">
                    {#each pageNumberLinks as link}
                        {#if link.active}
                            <span class="inline-flex items-center justify-center min-w-[32px] h-8 px-2 text-xs font-bold rounded-lg bg-[#003A70] dark:bg-[#FFB700] text-white dark:text-[#003A70] shadow-xs">
                                {@html link.label}
                            </span>
                        {:else if link.url}
                            <Link
                                href={link.url}
                                preserveState
                                preserveScroll
                                class="inline-flex items-center justify-center min-w-[32px] h-8 px-2 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-colors"
                            >
                                {@html link.label}
                            </Link>
                        {:else}
                            <span class="inline-flex items-center justify-center min-w-[28px] h-8 px-1 text-xs font-bold text-slate-400 dark:text-slate-500">
                                ...
                            </span>
                        {/if}
                    {/each}
                </div>
            {/if}

            <!-- Mobile Compact Current Page -->
            <span class="sm:hidden px-2 py-1 font-bold text-slate-700 dark:text-slate-300">
                {pagination.current_page} / {pagination.last_page}
            </span>

            <!-- Next Button -->
            {#if pagination.next_page_url}
                <Link
                    href={pagination.next_page_url}
                    preserveState
                    preserveScroll
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-colors shadow-2xs"
                >
                    <span>{t('pagination.next')}</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                </Link>
            {:else}
                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-100 dark:border-slate-800/80 bg-slate-50/60 dark:bg-slate-900/40 text-slate-400 dark:text-slate-600 cursor-not-allowed">
                    <span>{t('pagination.next')}</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                </span>
            {/if}
        </div>
    {/if}
</div>
