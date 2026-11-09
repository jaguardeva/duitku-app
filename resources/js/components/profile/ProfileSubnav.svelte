<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { t } from '@/lib/i18n';
    import { Shield, User } from '@lucide/svelte';

    interface Props {
        activeTab: 'info' | 'security';
    }

    let { activeTab }: Props = $props();

    const tabs = $derived([
        {
            id: 'info',
            label: t('navigation.profile'),
            href: '/app/profile',
            icon: User,
        },
        {
            id: 'security',
            label: t('navigation.security'),
            href: '/app/profile/security',
            icon: Shield,
        },
    ]);
</script>

<div class="flex items-center gap-2 sm:gap-4 border-b border-slate-200/80 dark:border-slate-800 text-xs overflow-x-auto scrollbar-none">
    {#each tabs as tab}
        {@const isActive = activeTab === tab.id}
        <Link
            href={tab.href}
            class={`px-3.5 py-2.5 font-medium flex items-center gap-2 border-b-2 transition-all whitespace-nowrap ${
                isActive
                    ? 'border-[#003A70] text-[#003A70] dark:border-[#FFB700] dark:text-[#FFB700] font-bold'
                    : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
            }`}
        >
            <tab.icon class="w-3.5 h-3.5" />
            <span>{tab.label}</span>
        </Link>
    {/each}
</div>
