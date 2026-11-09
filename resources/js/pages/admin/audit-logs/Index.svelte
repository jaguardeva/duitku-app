<script lang="ts">
    import Badge from '@/components/ui/Badge.svelte';
    import Button from '@/components/ui/Button.svelte';
    import Card from '@/components/ui/Card.svelte';
    import Input from '@/components/ui/Input.svelte';
    import Pagination, { type PaginationData } from '@/components/ui/Pagination.svelte';
    import SortableTh from '@/components/ui/SortableTh.svelte';
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import { debounce, formatDate } from '@/lib/utils';
    import { t } from '@/lib/i18n';
    import { Link, router } from '@inertiajs/svelte';
    import { Activity, Filter, RotateCcw, Search } from '@lucide/svelte';

    interface AuditLogItem {
        id: string;
        action: string;
        target_type?: string;
        target_id?: string;
        metadata?: Record<string, any>;
        ip_address?: string;
        user_agent?: string;
        created_at: string;
        admin?: { name: string; email: string };
    }

    interface AuditLogFilters {
        action?: string;
        per_page?: number;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    }

    interface Props {
        auditLogs?: PaginationData & { data: AuditLogItem[] };
        filters?: AuditLogFilters;
    }

    let {
        auditLogs = { data: [], current_page: 1, last_page: 1, per_page: 20, from: null, to: null, total: 0, links: [] },
        filters = {},
    }: Props = $props();

    function getInitialActionFilter(): string {
        return filters.action || '';
    }

    let actionFilter = $state(getInitialActionFilter());

    const currentSortBy = $derived(filters.sort_by || 'created_at');
    const currentSortDir = $derived((filters.sort_direction as 'asc' | 'desc') || 'desc');

    const debouncedApplyFilter = debounce(() => {
        applyFilter();
    }, 350);

    function handleInput(e: Event) {
        const target = e.target as HTMLInputElement;
        actionFilter = target.value;
        debouncedApplyFilter();
    }

    const hasActiveFilters = $derived(
        !!(actionFilter.trim() || currentSortBy !== 'created_at' || currentSortDir !== 'desc')
    );

    function handleSort(field: string) {
        let nextDir: 'asc' | 'desc' = 'asc';
        if (currentSortBy === field) {
            nextDir = currentSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            nextDir = field === 'created_at' ? 'desc' : 'asc';
        }

        router.get(
            '/admin/audit-logs',
            {
                action: actionFilter.trim() || undefined,
                per_page: filters.per_page !== 20 ? filters.per_page : undefined,
                sort_by: field !== 'created_at' || nextDir !== 'desc' ? field : undefined,
                sort_direction: field !== 'created_at' || nextDir !== 'desc' ? nextDir : undefined,
            },
            { preserveState: true, replace: true }
        );
    }

    function applyFilter() {
        router.get(
            '/admin/audit-logs',
            {
                action: actionFilter.trim() || undefined,
                per_page: filters.per_page !== 20 ? filters.per_page : undefined,
                sort_by: currentSortBy !== 'created_at' ? currentSortBy : undefined,
                sort_direction: currentSortDir !== 'desc' ? currentSortDir : undefined,
            },
            { preserveState: true, replace: true }
        );
    }

    function resetFilter() {
        actionFilter = '';
        router.get(
            '/admin/audit-logs',
            {
                per_page: filters.per_page !== 20 ? filters.per_page : undefined,
            },
            { preserveState: true, replace: true }
        );
    }
</script>

<AdminLayout title={t('admin.audit_logs')}>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100">
                    {t('admin.audit_logs')}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {t('admin_page.audit_description')}
                </p>
            </div>
        </div>

        <!-- Filter Card -->
        <Card class="p-4 sm:p-5">
            <!-- Filter Header with Conditional Reset Button -->
            <div class="flex items-center justify-between mb-3.5 pb-2.5 border-b border-slate-100 dark:border-slate-800/80">
                <div class="flex items-center gap-2">
                    <Filter class="w-4 h-4 text-slate-400" />
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{t('admin_page.filter_logs')}</span>
                    {#if hasActiveFilters}
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#003A70]/10 text-[#003A70] dark:bg-[#FFB700]/20 dark:text-[#FFD200]">
                            aktif
                        </span>
                    {/if}
                </div>

                {#if hasActiveFilters}
                    <button
                        type="button"
                        onclick={resetFilter}
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 transition-colors cursor-pointer animate-in fade-in duration-150"
                        title="Reset filter"
                    >
                        <RotateCcw class="w-3.5 h-3.5" />
                        {t('actions.reset_filter')}
                    </button>
                {/if}
            </div>

            <div class="w-full max-w-md">
                <Input
                    placeholder="Filter berdasarkan nama aksi (mis: user.suspended)..."
                    bind:value={actionFilter}
                    oninput={handleInput}
                    onchange={applyFilter}
                >
                    {#snippet leadingIcon()}
                        <Search class="w-4 h-4" />
                    {/snippet}
                </Input>
            </div>
        </Card>

        <!-- Top Pagination / Toolbar: Rows Per Page & Data Count -->
        <Pagination
            variant="top"
            pagination={auditLogs}
            itemName="aktivitas"
            perPageOptions={[10, 20, 50, 100]}
        />

        <!-- Audit Table -->
        <Card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[680px]">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <tr>
                            <SortableTh
                                field="created_at"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('admin_page.audit_time')}
                            </SortableTh>
                            <th class="py-3 px-4 select-none">{t('admin_page.actor')}</th>
                            <SortableTh
                                field="action"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('admin_page.action')}
                            </SortableTh>
                            <th class="py-3 px-4 select-none">{t('admin_page.metadata')}</th>
                            <SortableTh
                                field="ip_address"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('admin_page.ip')}
                            </SortableTh>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        {#each auditLogs.data as log (log.id)}
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 font-mono text-slate-500 text-[11px] whitespace-nowrap">
                                    {log.created_at.replace('T', ' ').substring(0, 19)}
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">
                                        {log.admin?.name || 'Sistem / Terhapus'}
                                    </div>
                                    <div class="text-slate-500 text-[11px]">
                                        {log.admin?.email || '-'}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <Badge variant="primary" size="sm" class="font-mono">
                                        {log.action}
                                    </Badge>
                                </td>

                                <td class="py-3.5 px-4 max-w-xs">
                                    {#if log.metadata}
                                        <pre class="text-[11px] font-mono bg-slate-100 dark:bg-slate-800/80 p-1.5 rounded overflow-x-auto max-h-16 text-slate-700 dark:text-slate-300">{JSON.stringify(log.metadata, null, 2)}</pre>
                                    {:else}
                                        <span class="text-slate-400">-</span>
                                    {/if}
                                </td>

                                <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500">
                                    {log.ip_address || '-'}
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    {t('empty.no_audit_logs')}
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Bottom Pagination: Page Navigation Buttons -->
        {#if auditLogs.last_page > 1}
            <Pagination
                variant="bottom"
                pagination={auditLogs}
            />
        {/if}
    </div>
</AdminLayout>
