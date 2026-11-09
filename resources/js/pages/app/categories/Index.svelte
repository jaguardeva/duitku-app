<script lang="ts">
    import Badge from '@/components/ui/Badge.svelte';
    import Button from '@/components/ui/Button.svelte';
    import Card from '@/components/ui/Card.svelte';
    import EmptyState from '@/components/ui/EmptyState.svelte';
    import Input from '@/components/ui/Input.svelte';
    import Modal from '@/components/ui/Modal.svelte';
    import Select from '@/components/ui/Select.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { t } from '@/lib/i18n';
    import { router, useForm } from '@inertiajs/svelte';
    import {
        Archive,
        ArrowDownLeft,
        ArrowUpRight,
        Briefcase,
        Car,
        CircleEllipsis,
        Film,
        FolderTree,
        Gift,
        GraduationCap,
        HeartPulse,
        Home,
        Plus,
        Receipt,
        ShoppingBag,
        Sparkles,
        TrendingUp,
        Utensils,
        Wallet,
    } from '@lucide/svelte';

    interface CategoryItem {
        id: string;
        user_id?: string;
        name: string;
        type: 'income' | 'expense';
        icon?: string;
        color_token?: string;
        is_system: boolean;
        is_archived: boolean;
    }

    interface Props {
        categories?: CategoryItem[];
    }

    let { categories = [] }: Props = $props();

    let activeType = $state<'expense' | 'income'>('expense');
    let isCreateModalOpen = $state(false);
    let editingCategory = $state<CategoryItem | null>(null);

    const form = useForm({
        name: '',
        type: 'expense',
        icon: 'circle-ellipsis',
        color_token: 'slate',
    });

    const editForm = useForm({
        name: '',
        icon: 'circle-ellipsis',
        color_token: 'slate',
    });

    const displayedCategories = $derived(
        categories.filter((c) => c.type === activeType)
    );

    const iconMap: Record<string, any> = {
        utensils: Utensils,
        car: Car,
        'shopping-bag': ShoppingBag,
        receipt: Receipt,
        'heart-pulse': HeartPulse,
        film: Film,
        'graduation-cap': GraduationCap,
        home: Home,
        'circle-ellipsis': CircleEllipsis,
        wallet: Wallet,
        gift: Gift,
        briefcase: Briefcase,
        sparkles: Sparkles,
        'trending-up': TrendingUp,
    };

    const iconOptions = [
        { value: 'utensils', label: t('categories.icons.utensils') },
        { value: 'car', label: t('categories.icons.car') },
        { value: 'shopping-bag', label: t('categories.icons.shopping') },
        { value: 'receipt', label: t('categories.icons.receipt') },
        { value: 'heart-pulse', label: t('categories.icons.health') },
        { value: 'film', label: t('categories.icons.film') },
        { value: 'graduation-cap', label: t('categories.icons.education') },
        { value: 'home', label: t('categories.icons.home') },
        { value: 'wallet', label: t('categories.icons.salary') },
        { value: 'gift', label: t('categories.icons.gift') },
        { value: 'briefcase', label: t('categories.icons.business') },
        { value: 'trending-up', label: t('categories.icons.investment') },
        { value: 'circle-ellipsis', label: t('categories.icons.other') },
    ];

    function openCreateModal() {
        form.reset();
        form.type = activeType;
        isCreateModalOpen = true;
    }

    function handleCreateSubmit(e: SubmitEvent) {
        e.preventDefault();
        form.post('/app/categories', {
            onSuccess: () => {
                isCreateModalOpen = false;
                form.reset();
            },
        });
    }

    function handleEdit(category: CategoryItem) {
        editingCategory = category;
        editForm.name = category.name;
        editForm.icon = category.icon || 'circle-ellipsis';
        editForm.color_token = category.color_token || 'slate';
    }

    function handleEditSubmit(e: SubmitEvent) {
        e.preventDefault();
        if (!editingCategory) return;
        editForm.put(`/app/categories/${editingCategory.id}`, {
            onSuccess: () => {
                editingCategory = null;
            },
        });
    }

    function handleToggleArchive(category: CategoryItem) {
        router.post(`/app/categories/${category.id}/archive`);
    }
</script>

<AppLayout title={t('categories.title')}>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                    {t('categories.heading')}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {t('categories.description')}
                </p>
            </div>
            <Button variant="primary" onclick={openCreateModal} class="text-xs font-semibold">
                <Plus class="w-4 h-4" />
                {t('categories.add_personal')}
            </Button>
        </div>

        <!-- Type Selector Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200/80 dark:border-slate-800 text-xs">
            <button
                type="button"
                onclick={() => (activeType = 'expense')}
                class={`px-4 py-2.5 font-semibold transition-colors border-b-2 cursor-pointer flex items-center gap-1.5 ${
                    activeType === 'expense'
                        ? 'border-rose-600 text-rose-600 dark:text-rose-400'
                        : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                }`}
            >
                <ArrowUpRight class="w-3.5 h-3.5" />
                {t('categories.expense')}
            </button>
            <button
                type="button"
                onclick={() => (activeType = 'income')}
                class={`px-4 py-2.5 font-semibold transition-colors border-b-2 cursor-pointer flex items-center gap-1.5 ${
                    activeType === 'income'
                        ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400'
                        : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                }`}
            >
                <ArrowDownLeft class="w-3.5 h-3.5" />
                {t('categories.income')}
            </button>
        </div>

        <!-- Categories List Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            {#each displayedCategories as cat (cat.id)}
                {@const Icon = (cat.icon && iconMap[cat.icon]) || CircleEllipsis}
                <Card class={`p-4 flex items-center justify-between ${cat.is_archived ? 'opacity-60 bg-slate-50/50 dark:bg-slate-900/40' : ''}`}>
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 shrink-0">
                            <Icon class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">
                                {cat.name}
                            </h4>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                {#if cat.is_system}
                                    <Badge variant="primary" size="sm" class="text-[10px]">{t('categories.default')}</Badge>
                                {:else}
                                    <Badge variant="default" size="sm" class="text-[10px]">{t('categories.personal')}</Badge>
                                {/if}
                                {#if cat.is_archived}
                                    <Badge variant="warning" size="sm" class="text-[10px]">{t('categories.archived')}</Badge>
                                {/if}
                            </div>
                        </div>
                    </div>

                    {#if !cat.is_system}
                        <div class="flex items-center gap-1 text-xs">
                            <button
                                type="button"
                                onclick={() => handleEdit(cat)}
                                class="px-2 py-1 rounded text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                            >
                                {t('actions.edit')}
                            </button>
                            <button
                                type="button"
                                onclick={() => handleToggleArchive(cat)}
                                class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer"
                                title={cat.is_archived ? t('actions.restore') : t('actions.archive')}
                                aria-label={cat.is_archived ? t('actions.restore') : t('actions.archive')}
                            >
                                <Archive class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    {/if}
                </Card>
            {/each}
        </div>
    </div>

    <!-- Create Custom Category Modal -->
    <Modal
        bind:open={isCreateModalOpen}
        title={`${t('actions.add')} ${t(activeType === 'expense' ? 'domain.expense' : 'domain.income')} ${t('domain.category').toLowerCase()}`}
        size="md"
    >
        <form onsubmit={handleCreateSubmit} class="space-y-4">
            <Input
                label={t('categories.name')}
                placeholder={t('categories.name_placeholder')}
                required
                maxlength={80}
                bind:value={form.name}
                error={form.errors.name}
            />

            <Select
                label={t('categories.icon')}
                options={iconOptions}
                required
                bind:value={form.icon}
                error={form.errors.icon}
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
                    {t('actions.create')} {t('domain.category')}
                </Button>
            </div>
        </form>
    </Modal>

    <!-- Edit Custom Category Modal -->
    <Modal
        open={editingCategory !== null}
        onclose={() => (editingCategory = null)}
        title={`${t('actions.edit')} ${t('categories.personal').toLowerCase()} ${t('domain.category').toLowerCase()}`}
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
</AppLayout>
