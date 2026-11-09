<script lang="ts">
    import Badge from '@/components/ui/Badge.svelte';
    import Button from '@/components/ui/Button.svelte';
    import Card from '@/components/ui/Card.svelte';
    import Input from '@/components/ui/Input.svelte';
    import Modal from '@/components/ui/Modal.svelte';
    import Pagination, { type PaginationData } from '@/components/ui/Pagination.svelte';
    import Select from '@/components/ui/Select.svelte';
    import SortableTh from '@/components/ui/SortableTh.svelte';
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import { debounce, formatDate } from '@/lib/utils';
    import { t } from '@/lib/i18n';
    import { Link, router, useForm } from '@inertiajs/svelte';
    import {
        AlertOctagon,
        CheckCircle2,
        Filter,
        RotateCcw,
        Search,
        UserCheck,
    } from '@lucide/svelte';

    interface UserItem {
        id: string;
        name: string;
        email: string;
        role: string;
        status: string;
        timezone: string;
        default_currency: string;
        email_verified_at: string | null;
        created_at: string;
    }

    interface UserFilters {
        search?: string;
        status?: string;
        verification?: string;
        per_page?: number;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    }

    interface Props {
        users?: PaginationData & { data: UserItem[] };
        filters?: UserFilters;
    }

    let {
        users = { data: [], current_page: 1, last_page: 1, per_page: 15, from: null, to: null, total: 0, links: [] },
        filters = {},
    }: Props = $props();

    function getInitialFilters(): {
        search: string;
        status: string;
        verification: string;
    } {
        return {
            search: filters.search || '',
            status: filters.status || '',
            verification: filters.verification || '',
        };
    }

    const initialFilters = getInitialFilters();
    let search = $state(initialFilters.search);
    let status = $state(initialFilters.status);
    let verification = $state(initialFilters.verification);

    const currentSortBy = $derived(filters.sort_by || 'created_at');
    const currentSortDir = $derived((filters.sort_direction as 'asc' | 'desc') || 'desc');

    let targetUser = $state<UserItem | null>(null);

    const suspensionForm = useForm({
        reason: '',
    });

    const debouncedApplyFilters = debounce(() => {
        applyFilters();
    }, 350);

    function handleSearchInput(e: Event) {
        const target = e.target as HTMLInputElement;
        search = target.value;
        debouncedApplyFilters();
    }

    const hasActiveFilters = $derived(
        !!(
            search.trim() ||
            status ||
            verification ||
            currentSortBy !== 'created_at' ||
            currentSortDir !== 'desc'
        )
    );

    const activeFiltersCount = $derived(
        [
            search.trim(),
            status,
            verification,
            currentSortBy !== 'created_at' || currentSortDir !== 'desc' ? 'sort' : '',
        ].filter(Boolean).length
    );

    function handleSort(field: string) {
        let nextDir: 'asc' | 'desc' = 'asc';
        if (currentSortBy === field) {
            nextDir = currentSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            nextDir = field === 'created_at' ? 'desc' : 'asc';
        }

        router.get(
            '/admin/users',
            {
                search: search.trim() || undefined,
                status: status || undefined,
                verification: verification || undefined,
                per_page: filters.per_page !== 15 ? filters.per_page : undefined,
                sort_by: field !== 'created_at' || nextDir !== 'desc' ? field : undefined,
                sort_direction: field !== 'created_at' || nextDir !== 'desc' ? nextDir : undefined,
            },
            { preserveState: true, replace: true }
        );
    }

    function applyFilters() {
        router.get(
            '/admin/users',
            {
                search: search.trim() || undefined,
                status: status || undefined,
                verification: verification || undefined,
                per_page: filters.per_page !== 15 ? filters.per_page : undefined,
                sort_by: currentSortBy !== 'created_at' ? currentSortBy : undefined,
                sort_direction: currentSortDir !== 'desc' ? currentSortDir : undefined,
            },
            { preserveState: true, replace: true }
        );
    }

    function resetFilters() {
        search = '';
        status = '';
        verification = '';
        router.get(
            '/admin/users',
            {
                per_page: filters.per_page !== 15 ? filters.per_page : undefined,
            },
            { preserveState: true, replace: true }
        );
    }

    function openSuspensionModal(u: UserItem) {
        targetUser = u;
        suspensionForm.reset();
    }

    function handleSuspensionSubmit(e: SubmitEvent) {
        e.preventDefault();
        if (!targetUser) return;

        suspensionForm.post(`/admin/users/${targetUser.id}/suspension`, {
            onSuccess: () => {
                targetUser = null;
            },
        });
    }

    const statusOptions = [
        { value: '', label: t('admin_page.all_account_status') },
        { value: 'active', label: t('admin_page.active') },
        { value: 'suspended', label: t('admin_page.suspended') },
    ];

    const verificationOptions = [
        { value: '', label: t('admin_page.all_verification_status') },
        { value: 'verified', label: t('admin_page.verified') },
        { value: 'unverified', label: t('admin_page.not_verified') },
    ];
</script>

<AdminLayout title={t('admin.users')}>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100">
                    {t('admin_page.users_heading')}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {t('admin_page.users_description')}
                </p>
            </div>
        </div>

        <!-- Filter Card -->
        <Card class="p-4 sm:p-5">
            <!-- Filter Header with Conditional Reset Button -->
            <div class="flex items-center justify-between mb-3.5 pb-2.5 border-b border-slate-100 dark:border-slate-800/80">
                <div class="flex items-center gap-2">
                    <Filter class="w-4 h-4 text-slate-400" />
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{t('admin_page.filter_users')}</span>
                    {#if hasActiveFilters}
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#003A70]/10 text-[#003A70] dark:bg-[#FFB700]/20 dark:text-[#FFD200]">
                            {activeFiltersCount} aktif
                        </span>
                    {/if}
                </div>

                {#if hasActiveFilters}
                    <button
                        type="button"
                        onclick={resetFilters}
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 transition-colors cursor-pointer animate-in fade-in duration-150"
                        title="Reset semua filter aktif"
                    >
                        <RotateCcw class="w-3.5 h-3.5" />
                        {t('actions.reset_filter')}
                    </button>
                {/if}
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="sm:col-span-2 lg:col-span-2">
                    <Input
                        placeholder={t('admin_page.search_users')}
                        bind:value={search}
                        oninput={handleSearchInput}
                        onchange={applyFilters}
                    >
                        {#snippet leadingIcon()}
                            <Search class="w-4 h-4" />
                        {/snippet}
                    </Input>
                </div>

                <Select
                    options={statusOptions}
                    bind:value={status}
                    onchange={applyFilters}
                />

                <Select
                    options={verificationOptions}
                    bind:value={verification}
                    onchange={applyFilters}
                />
            </div>
        </Card>

        <!-- Top Pagination / Toolbar: Rows Per Page & Data Count -->
        <Pagination
            variant="top"
            pagination={users}
            itemName="pengguna"
            perPageOptions={[10, 15, 25, 50, 100]}
        />

        <!-- Users Table -->
        <Card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[720px]">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <tr>
                            <SortableTh
                                field="name"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('admin_page.user_name_email')}
                            </SortableTh>
                            <SortableTh
                                field="status"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('admin_page.account_status')}
                            </SortableTh>
                            <SortableTh
                                field="email_verified_at"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('admin_page.verification')}
                            </SortableTh>
                            <th class="py-3 px-4 select-none">{t('admin_page.timezone_currency')}</th>
                            <SortableTh
                                field="created_at"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('admin_page.joined')}
                            </SortableTh>
                            <th class="py-3 px-4 text-right select-none">{t('admin_page.action')}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        {#each users.data as u (u.id)}
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{u.name}</div>
                                    <div class="text-slate-500 dark:text-slate-400 text-[11px]">{u.email}</div>
                                </td>

                                <td class="py-3.5 px-4">
                                    {#if u.status === 'active'}
                                        <Badge variant="success" size="sm">{t('admin_page.active')}</Badge>
                                    {:else}
                                        <Badge variant="danger" size="sm">{t('admin_page.suspended')}</Badge>
                                    {/if}
                                </td>

                                <td class="py-3.5 px-4">
                                    {#if u.email_verified_at}
                                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                                            <CheckCircle2 class="w-3.5 h-3.5" />
                                            {t('admin_page.verified')}
                                        </span>
                                    {:else}
                                        <span class="text-amber-600 dark:text-amber-400 font-medium">
                                            {t('admin_page.not_verified')}
                                        </span>
                                    {/if}
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="text-slate-700 dark:text-slate-300">{u.timezone}</div>
                                    <div class="font-mono text-[11px] text-slate-500">{u.default_currency}</div>
                                </td>

                                <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                    {formatDate(u.created_at)}
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <Button
                                        size="sm"
                                        variant={u.status === 'active' ? 'destructive' : 'secondary'}
                                        class="text-xs"
                                        onclick={() => openSuspensionModal(u)}
                                    >
                                        {#if u.status === 'active'}
                                            <AlertOctagon class="w-3.5 h-3.5" />
                                            {t('admin_page.suspend')}
                                        {:else}
                                            <UserCheck class="w-3.5 h-3.5" />
                                            {t('admin_page.activate')}
                                        {/if}
                                    </Button>
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    {t('empty.no_users')}
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Bottom Pagination: Page Navigation Buttons -->
        {#if users.last_page > 1}
            <Pagination
                variant="bottom"
                pagination={users}
            />
        {/if}
    </div>

    <!-- Suspension Confirmation Modal -->
    <Modal
        open={targetUser !== null}
        onclose={() => (targetUser = null)}
        title={targetUser?.status === 'active' ? t('admin_page.suspend_title') : t('admin_page.activate_title')}
        size="md"
    >
        {#if targetUser}
            <form onsubmit={handleSuspensionSubmit} class="space-y-4">
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    {#if targetUser.status === 'active'}
                        {t('admin_page.suspend_message', { name: targetUser.name, email: targetUser.email })}
                    {:else}
                        {t('admin_page.activate_message', { name: targetUser.name, email: targetUser.email })}
                    {/if}
                </p>

                <Input
                    label={t('admin_page.reason')}
                    placeholder={t('admin_page.reason_placeholder')}
                    maxlength={255}
                    bind:value={suspensionForm.reason}
                />

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        onclick={() => (targetUser = null)}
                    >
                        {t('common.cancel')}
                    </Button>
                    <Button
                        type="submit"
                        variant={targetUser.status === 'active' ? 'destructive' : 'primary'}
                        loading={suspensionForm.processing}
                    >
                        {targetUser.status === 'active' ? t('admin_page.confirm_suspend') : t('admin_page.confirm_activate')}
                    </Button>
                </div>
            </form>
        {/if}
    </Modal>
</AdminLayout>
