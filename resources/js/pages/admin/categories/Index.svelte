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
    import { debounce } from '@/lib/utils';
    import { router, useForm } from '@inertiajs/svelte';
    import { t } from '@/lib/i18n';
    import {
        Archive,
        Briefcase,
        Car,
        CircleEllipsis,
        Film,
        Filter,
        Gift,
        GraduationCap,
        HeartPulse,
        Home,
        Plus,
        Receipt,
        RotateCcw,
        Search,
        ShoppingBag,
        TrendingUp,
        Utensils,
        Wallet,
    } from '@lucide/svelte';

    interface CategoryItem {
        id: string;
        name: string;
        type: 'income' | 'expense';
        icon?: string;
        color_token?: string;
        sort_order: number;
        is_archived: boolean;
    }

    interface CategoryFilters {
        search?: string;
        type?: string;
        status?: string;
        per_page?: number;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    }

    interface Props {
        categories?: PaginationData & { data: CategoryItem[] };
        filters?: CategoryFilters;
    }

    let {
        categories = {
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 10,
            from: null,
            to: null,
            total: 0,
            links: [],
        },
        filters = {},
    }: Props = $props();

    function getInitialFilters(): {
        search: string;
        type: string;
        status: string;
    } {
        return {
            search: filters.search || '',
            type: filters.type || '',
            status: filters.status || '',
        };
    }

    const initialFilters = getInitialFilters();
    let search = $state(initialFilters.search);
    let type = $state(initialFilters.type);
    let status = $state(initialFilters.status);

    const currentSortBy = $derived(filters.sort_by || 'sort_order');
    const currentSortDir = $derived((filters.sort_direction as 'asc' | 'desc') || 'asc');

    let isCreateModalOpen = $state(false);
    let editingCategory = $state<CategoryItem | null>(null);

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
            type ||
            status ||
            currentSortBy !== 'sort_order' ||
            currentSortDir !== 'asc'
        )
    );

    const activeFiltersCount = $derived(
        [
            search.trim(),
            type,
            status,
            currentSortBy !== 'sort_order' || currentSortDir !== 'asc' ? 'sort' : '',
        ].filter(Boolean).length
    );

    function handleSort(field: string) {
        let nextDir: 'asc' | 'desc' = 'asc';
        if (currentSortBy === field) {
            nextDir = currentSortDir === 'asc' ? 'desc' : 'asc';
        }

        router.get(
            '/admin/categories',
            {
                search: search.trim() || undefined,
                type: type || undefined,
                status: status || undefined,
                per_page: filters.per_page !== 10 ? filters.per_page : undefined,
                sort_by: field !== 'sort_order' || nextDir !== 'asc' ? field : undefined,
                sort_direction: field !== 'sort_order' || nextDir !== 'asc' ? nextDir : undefined,
            },
            { preserveState: true, replace: true }
        );
    }

    function applyFilters() {
        router.get(
            '/admin/categories',
            {
                search: search.trim() || undefined,
                type: type || undefined,
                status: status || undefined,
                per_page: filters.per_page !== 10 ? filters.per_page : undefined,
                sort_by: currentSortBy !== 'sort_order' ? currentSortBy : undefined,
                sort_direction: currentSortDir !== 'asc' ? currentSortDir : undefined,
            },
            { preserveState: true, replace: true }
        );
    }

    function resetFilters() {
        search = '';
        type = '';
        status = '';
        router.get(
            '/admin/categories',
            {
                per_page: filters.per_page !== 10 ? filters.per_page : undefined,
            },
            { preserveState: true, replace: true }
        );
    }

    const form = useForm({
        name: '',
        type: 'expense',
        icon: 'circle-ellipsis',
        color_token: 'blue',
        sort_order: 0,
    });

    const editForm = useForm({
        name: '',
        icon: 'circle-ellipsis',
        color_token: 'blue',
        sort_order: 0,
    });

    const iconMap: Record<string, any> = {
        utensils: Utensils,
        car: Car,
        'shopping-bag': ShoppingBag,
        receipt: Receipt,
        'heart-pulse': HeartPulse,
        film: Film,
        'graduation-cap': GraduationCap,
        home: Home,
        wallet: Wallet,
        gift: Gift,
        briefcase: Briefcase,
        'trending-up': TrendingUp,
        'circle-ellipsis': CircleEllipsis,
    };

    const iconOptions = [
        { value: 'utensils', label: t('categories.icons.utensils') }, { value: 'car', label: t('categories.icons.car') }, { value: 'shopping-bag', label: t('categories.icons.shopping') },
        { value: 'receipt', label: t('categories.icons.receipt') }, { value: 'heart-pulse', label: t('categories.icons.health') }, { value: 'film', label: t('categories.icons.film') },
        { value: 'graduation-cap', label: t('categories.icons.education') }, { value: 'home', label: t('categories.icons.home') }, { value: 'wallet', label: t('categories.icons.salary') },
        { value: 'gift', label: t('categories.icons.gift') }, { value: 'briefcase', label: t('categories.icons.business') }, { value: 'trending-up', label: t('categories.icons.investment') }, { value: 'circle-ellipsis', label: t('categories.icons.other') },
    ];

    const typeOptions = [
        { value: 'expense', label: `${t('domain.expense')} (Expense)` },
        { value: 'income', label: `${t('domain.income')} (Income)` },
    ];

    const filterTypeOptions = [
        { value: '', label: t('admin_page.all_category_types') },
        { value: 'expense', label: `${t('domain.expense')} (Expense)` },
        { value: 'income', label: `${t('domain.income')} (Income)` },
    ];

    const filterStatusOptions = [
        { value: '', label: t('admin_page.all_category_status') },
        { value: 'active', label: t('admin_page.active') },
        { value: 'archived', label: t('categories.archived') },
    ];

    function handleCreateSubmit(e: SubmitEvent) {
        e.preventDefault();
        form.post('/admin/categories', {
            onSuccess: () => {
                isCreateModalOpen = false;
                form.reset();
            },
        });
    }

    function handleEdit(c: CategoryItem) {
        editingCategory = c;
        editForm.name = c.name;
        editForm.icon = c.icon || 'circle-ellipsis';
        editForm.color_token = c.color_token || 'blue';
        editForm.sort_order = c.sort_order;
    }

    function handleEditSubmit(e: SubmitEvent) {
        e.preventDefault();
        if (!editingCategory) return;

        editForm.put(`/admin/categories/${editingCategory.id}`, {
            onSuccess: () => {
                editingCategory = null;
            },
        });
    }

    function handleToggleArchive(c: CategoryItem) {
        router.post(`/admin/categories/${c.id}/archive`);
    }
</script>

<AdminLayout title={t('admin.categories')}>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100">
                    {t('admin_page.system_category')}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {t('admin_page.system_description')}
                </p>
            </div>
            <Button variant="primary" onclick={() => (isCreateModalOpen = true)} class="text-xs font-semibold shrink-0 self-start sm:self-auto">
                <Plus class="w-4 h-4" />
                <span>{t('admin_page.add_system_category')}</span>
            </Button>
        </div>

        <!-- Filter Card -->
        <Card class="p-4 sm:p-5">
            <!-- Filter Header with Conditional Reset Button -->
            <div class="flex items-center justify-between mb-3.5 pb-2.5 border-b border-slate-100 dark:border-slate-800/80">
                <div class="flex items-center gap-2">
                    <Filter class="w-4 h-4 text-slate-400" />
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{t('admin_page.filter_categories')}</span>
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
                        title={t('actions.reset_filter')}
                    >
                        <RotateCcw class="w-3.5 h-3.5" />
                        {t('actions.reset_filter')}
                    </button>
                {/if}
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="sm:col-span-2 lg:col-span-2">
                    <Input
                        placeholder={t('admin_page.search_categories')}
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
                    options={filterTypeOptions}
                    bind:value={type}
                    onchange={applyFilters}
                />

                <Select
                    options={filterStatusOptions}
                    bind:value={status}
                    onchange={applyFilters}
                />
            </div>
        </Card>

        <!-- Top Pagination / Toolbar: Rows Per Page & Data Count -->
        <Pagination
            variant="top"
            pagination={categories}
            itemName={t('domain.category').toLowerCase()}
            perPageOptions={[10, 25, 50, 100]}
        />

        <!-- Table -->
        <Card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[640px]">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <tr>
                            <SortableTh
                                field="name"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('categories.name')}
                            </SortableTh>
                            <SortableTh
                                field="type"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('domain.category')}
                            </SortableTh>
                            <th class="py-3 px-4 select-none">{t('admin_page.icon_whitelist')}</th>
                            <SortableTh
                                field="sort_order"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('admin_page.sort_order')}
                            </SortableTh>
                            <SortableTh
                                field="is_archived"
                                currentSortBy={currentSortBy}
                                currentSortDir={currentSortDir}
                                onsort={handleSort}
                            >
                                {t('admin_page.account_status')}
                            </SortableTh>
                            <th class="py-3 px-4 text-right select-none">{t('admin_page.action')}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        {#each categories.data as cat (cat.id)}
                            {@const IconComp = (cat.icon && iconMap[cat.icon]) || CircleEllipsis}
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-slate-100">
                                    {cat.name}
                                </td>
                                <td class="py-3.5 px-4">
                                    {#if cat.type === 'income'}
                                        <Badge variant="success" size="sm">{t('domain.income')}</Badge>
                                    {:else}
                                        <Badge variant="danger" size="sm">{t('domain.expense')}</Badge>
                                    {/if}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300">
                                            <IconComp class="w-3.5 h-3.5" />
                                        </span>
                                        <span class="font-mono text-xs text-slate-500">{cat.icon || '-'}</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono">
                                    {cat.sort_order}
                                </td>
                                <td class="py-3.5 px-4">
                                    {#if cat.is_archived}
                                        <Badge variant="warning" size="sm">{t('categories.archived')}</Badge>
                                    {:else}
                                        <Badge variant="primary" size="sm">{t('admin_page.active')}</Badge>
                                    {/if}
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="text-xs"
                                            onclick={() => handleEdit(cat)}
                                        >
                                            {t('actions.edit')}
                                        </Button>
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            class="text-xs"
                                            onclick={() => handleToggleArchive(cat)}
                                        >
                                            <Archive class="w-3.5 h-3.5" />
                                            {cat.is_archived ? t('actions.restore') : t('actions.archive')}
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                            {hasActiveFilters ? t('empty.no_matching_categories') : t('empty.no_categories')}
                                        </p>
                                        {#if hasActiveFilters}
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                class="text-xs mt-1"
                                                onclick={resetFilters}
                                            >
                                                <RotateCcw class="w-3.5 h-3.5" />
                                                {t('actions.reset_filter')}
                                            </Button>
                                        {/if}
                                    </div>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Bottom Pagination: Page Navigation Buttons -->
        {#if categories.last_page > 1}
            <Pagination
                variant="bottom"
                pagination={categories}
            />
        {/if}
    </div>

    <!-- Create System Category Modal -->
    <Modal
        bind:open={isCreateModalOpen}
        title={t('admin_page.add_system_category')}
        size="md"
    >
        <form onsubmit={handleCreateSubmit} class="space-y-4">
            <Input
                label={t('categories.name')}
                required
                maxlength={80}
                bind:value={form.name}
                error={form.errors.name}
            />

            <Select
                label={t('domain.category')}
                options={typeOptions}
                required
                bind:value={form.type}
                error={form.errors.type}
            />

            <Select
                label={t('categories.icon')}
                options={iconOptions}
                required
                bind:value={form.icon}
                error={form.errors.icon}
            />

            <Input
                label={t('admin_page.sort_order')}
                type="number"
                min="0"
                bind:value={form.sort_order}
                error={form.errors.sort_order}
            />

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                <Button
                    type="button"
                    variant="outline"
                    onclick={() => (isCreateModalOpen = false)}
                >
                    {t('common.cancel')}
                </Button>
                <Button
                    type="submit"
                    variant="primary"
                    loading={form.processing}
                >
                    {t('common.save')}
                </Button>
            </div>
        </form>
    </Modal>

    <!-- Edit System Category Modal -->
    <Modal
        open={editingCategory !== null}
        onclose={() => (editingCategory = null)}
        title={`${t('actions.edit')} ${t('admin.categories')}`}
        size="md"
    >
        <form onsubmit={handleEditSubmit} class="space-y-4">
            <Input
                label={t('categories.name')}
                required
                maxlength={80}
                bind:value={editForm.name}
                error={editForm.errors.name}
            />

            <Select
                label={t('categories.icon')}
                options={iconOptions}
                required
                bind:value={editForm.icon}
                error={editForm.errors.icon}
            />

            <Input
                label={t('admin_page.sort_order')}
                type="number"
                min="0"
                bind:value={editForm.sort_order}
                error={editForm.errors.sort_order}
            />

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                <Button
                    type="button"
                    variant="outline"
                    onclick={() => (editingCategory = null)}
                >
                    {t('common.cancel')}
                </Button>
                <Button
                    type="submit"
                    variant="primary"
                    loading={editForm.processing}
                >
                    {t('actions.update')} {t('domain.category')}
                </Button>
            </div>
        </form>
    </Modal>
</AdminLayout>
