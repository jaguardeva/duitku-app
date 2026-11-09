<script lang="ts">
    import AboutModal from '@/components/ui/AboutModal.svelte';
    import BalanceToggle from '@/components/ui/BalanceToggle.svelte';
    import ConfirmDialog from '@/components/ui/ConfirmDialog.svelte';
    import LanguageSwitcher from '@/components/ui/LanguageSwitcher.svelte';
    import SeoHead from '@/components/SeoHead.svelte';
    import ThemeToggle from '@/components/ui/ThemeToggle.svelte';
    import { balanceVisibility } from '@/lib/balanceVisibility.svelte';
    import { t } from '@/lib/i18n';
    import { themeManager } from '@/lib/theme.svelte';
    import { Link, page, router } from '@inertiajs/svelte';
    import {
        ArrowLeftRight,
        BarChart3,
        FolderTree,
        Info,
        LayoutDashboard,
        LogOut,
        Moon,
        PieChart,
        Receipt,
        Shield,
        Sun,
        User as UserIcon,
        Wallet,
    } from '@lucide/svelte';
    import type { Snippet } from 'svelte';

    interface Props {
        title?: string;
        children?: Snippet;
    }

    let {
        title = 'Dashboard',
        children,
    }: Props = $props();

    const appName = $derived(page.props.name || 'CatatDuit');
    const fullTitle = $derived(title ? `${title} - ${appName}` : appName);

    const user = $derived(page.props.auth?.user);
    const flash = $derived(page.props.flash as { success?: string; error?: string } | undefined);
    const currentUrl = $derived(page.url);

    let isProfileMenuOpen = $state(false);
    let profileMenuContainer = $state<HTMLDivElement | null>(null);

    function toggleProfileMenu() {
        isProfileMenuOpen = !isProfileMenuOpen;
    }

    function handleWindowClick(e: MouseEvent) {
        if (isProfileMenuOpen && profileMenuContainer && !profileMenuContainer.contains(e.target as Node)) {
            isProfileMenuOpen = false;
        }
    }

    function handleWindowKeydown(e: KeyboardEvent) {
        if (e.key === 'Escape' && isProfileMenuOpen) {
            isProfileMenuOpen = false;
        }
    }

    let isLogoutConfirmOpen = $state(false);
    let isLoggingOut = $state(false);
    let isAboutModalOpen = $state(false);

    function handleLogoutClick() {
        isProfileMenuOpen = false;
        isLogoutConfirmOpen = true;
    }

    function confirmLogout() {
        isLoggingOut = true;
        router.post('/logout', {}, {
            onFinish: () => {
                isLoggingOut = false;
                isLogoutConfirmOpen = false;
            },
        });
    }

    const navItems = $derived([
        { label: t('navigation.dashboard'), href: '/app', icon: LayoutDashboard, exact: true },
        { label: t('navigation.transactions'), href: '/app/transactions', icon: Receipt },
        { label: t('navigation.wallets'), href: '/app/wallets', icon: Wallet },
        { label: t('navigation.transfers'), href: '/app/transfers', icon: ArrowLeftRight },
        { label: t('navigation.budgets'), href: '/app/budgets', icon: PieChart },
        { label: t('navigation.categories'), href: '/app/categories', icon: FolderTree },
        { label: t('navigation.reports'), href: '/app/reports', icon: BarChart3 },
    ]);

    function isActive(itemHref: string, exact = false): boolean {
        if (exact) {
            return currentUrl === itemHref;
        }
        return currentUrl.startsWith(itemHref);
    }
</script>

<SeoHead {title} noindex={true} />

<svelte:window onclick={handleWindowClick} onkeydown={handleWindowKeydown} />

<div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col">
    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-30 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
            <!-- Kolom 1: Brand Logo (Left) -->
            <div class="flex items-center justify-start shrink-0 md:flex-1">
                <Link href="/app" class="flex items-center gap-2.5 group">
                    <img
                        src="/favicon.png"
                        alt={appName}
                        class="h-9 w-9 sm:h-12 sm:w-12 rounded-xl object-contain shadow-sm shadow-[#FFB700]/25 group-hover:scale-105 transition-transform duration-200"
                    />
                </Link>
            </div>

            <!-- Kolom 2: Desktop Navigation Links (Center) -->
            <nav class="hidden md:flex items-center justify-center gap-0.5 lg:gap-1 shrink-0">
                {#each navItems as item}
                    {@const active = isActive(item.href, item.exact)}
                    <Link
                        href={item.href}
                        class={`px-2 lg:px-3 py-1.5 rounded-lg text-[11px] lg:text-xs font-semibold tracking-wide transition-all duration-150 inline-flex items-center gap-1 lg:gap-1.5 ${
                            active
                                ? 'bg-[#003A70]/10 dark:bg-[#0C3B67] text-[#003A70] dark:text-[#FFD200] font-bold shadow-xs'
                                : 'text-slate-600 dark:text-slate-300 hover:text-[#003A70] dark:hover:text-[#FFD200] hover:bg-slate-100 dark:hover:bg-slate-800'
                        }`}
                    >
                        <item.icon class="w-3.5 h-3.5" />
                        {item.label}
                    </Link>
                {/each}
            </nav>

            <!-- Kolom 3: Right Controls: Admin Link & User Menu (Right) -->
            <div class="flex items-center justify-end gap-2 shrink-0 md:flex-1">
                {#if user?.role === 'admin'}
                    <Link
                        href="/admin"
                        class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 text-xs font-semibold hover:bg-amber-100 transition-colors"
                        title={t('navigation.admin')}
                    >
                        <Shield class="w-3.5 h-3.5" />
                        {t('navigation.admin')}
                    </Link>
                {/if}

                <!-- User Profile Dropdown -->
                <div class="relative" bind:this={profileMenuContainer}>
                    <button
                        type="button"
                        onclick={toggleProfileMenu}
                        class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer select-none"
                    >
                        <div class="w-8 h-8 rounded-full bg-[#003A70] text-[#FFB700] font-bold text-xs flex items-center justify-center border border-[#FFB700]/30 shadow-xs">
                            {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
                        </div>
                    </button>

                    {#if isProfileMenuOpen}
                        <div class="absolute right-0 mt-2 w-60 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xl py-1.5 z-50 text-xs animate-in zoom-in-95 duration-150">
                            <!-- User Header -->
                            <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                                <p class="font-semibold text-slate-900 dark:text-slate-100 truncate">{user?.name}</p>
                                <p class="text-slate-500 dark:text-slate-400 truncate text-[11px] mt-0.5">{user?.email}</p>
                            </div>

                            <!-- Theme Toggle Row -->
                            <div class="px-4 py-2.5 flex items-center justify-between text-slate-700 dark:text-slate-300 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <span class="flex items-center gap-2 font-medium">
                                    {#if themeManager.theme === 'dark'}
                                        <Moon class="w-4 h-4 text-[#FFB700]" />
                        <span>{t('theme.dark')}</span>
                                    {:else}
                                        <Sun class="w-4 h-4 text-amber-500" />
                        <span>{t('theme.light')}</span>
                                    {/if}
                                </span>
                                <ThemeToggle />
                            </div>

                            <!-- Balance Visibility Toggle Row -->
                            <div class="px-4 py-2.5 flex items-center justify-between text-slate-700 dark:text-slate-300 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <span class="flex items-center gap-2 font-medium">
                                    <span>{balanceVisibility.isHidden ? t('balance.hidden') : t('balance.visible')}</span>
                                </span>
                                <BalanceToggle variant="switch" />
                            </div>

                            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>

                            <!-- Nav Links -->
                            <Link
                                href="/app/profile"
                                class="flex items-center gap-2 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors font-medium"
                                onclick={() => (isProfileMenuOpen = false)}
                            >
                                <UserIcon class="w-4 h-4 text-slate-400" />
                                {t('navigation.profile')}
                            </Link>

                            <Link
                                href="/app/profile/security"
                                class="flex items-center gap-2 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors font-medium"
                                onclick={() => (isProfileMenuOpen = false)}
                            >
                                <Shield class="w-4 h-4 text-slate-400" />
                                {t('navigation.security')}
                            </Link>

                            <button
                                type="button"
                                class="w-full flex items-center gap-2 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors font-medium cursor-pointer text-left"
                                onclick={() => {
                                    isProfileMenuOpen = false;
                                    isAboutModalOpen = true;
                                }}
                            >
                                <Info class="w-4 h-4 text-slate-400" />
                                {t('navigation.about')}
                            </button>

                            {#if user?.role === 'admin'}
                                <Link
                                    href="/admin"
                                    class="flex items-center gap-2 px-4 py-2 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition-colors font-semibold"
                                    onclick={() => (isProfileMenuOpen = false)}
                                >
                                    <Shield class="w-4 h-4" />
                                    {t('navigation.admin')}
                                </Link>
                            {/if}

                            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>

                            <button
                                type="button"
                                onclick={handleLogoutClick}
                                class="w-full flex items-center gap-2 px-4 py-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer font-semibold text-left"
                            >
                                <LogOut class="w-4 h-4" />
                                {t('auth.logout')}
                            </button>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </header>

    <!-- Flash notification toast / banner -->
    {#if flash?.success}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/60 text-xs font-medium text-emerald-800 dark:text-emerald-300 flex items-center justify-between shadow-xs">
                <span>{flash.success}</span>
            </div>
        </div>
    {/if}

    {#if flash?.error}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/60 text-xs font-medium text-rose-800 dark:text-rose-300 flex items-center justify-between shadow-xs">
                <span>{flash.error}</span>
            </div>
        </div>
    {/if}

    <!-- Main Content Viewport -->
    <main class="flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 pb-24 md:pb-12">
        {@render children?.()}
    </main>

    <!-- Mobile Bottom Navigation Bar (Thumb friendly for mobile viewports) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200/80 dark:border-slate-800 px-2 py-1 flex items-center justify-around shadow-lg">
        {#each navItems.slice(0, 5) as item}
            {@const active = isActive(item.href, item.exact)}
            <Link
                href={item.href}
                class={`flex flex-col items-center justify-center py-1 px-2 rounded-lg text-[10px] font-medium transition-colors ${
                    active
                        ? 'text-[#003A70] dark:text-[#FFB700] font-bold'
                        : 'text-slate-500 dark:text-slate-400'
                }`}
            >
                <item.icon class="w-5 h-5 mb-0.5" />
                <span>{item.label}</span>
            </Link>
        {/each}
    </nav>

    <!-- Logout Confirmation Dialog -->
    <ConfirmDialog
        bind:open={isLogoutConfirmOpen}
        title={t('auth.logout_confirm_title')}
        message={t('auth.logout_confirm_message')}
        confirmText={t('auth.logout')}
        cancelText={t('common.cancel')}
        variant="danger"
        iconType="logout"
        loading={isLoggingOut}
        onconfirm={confirmLogout}
    />

    <!-- About Application Modal -->
    <AboutModal bind:open={isAboutModalOpen} />
</div>
