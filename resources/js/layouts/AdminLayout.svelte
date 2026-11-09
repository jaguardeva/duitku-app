<script lang="ts">
    import ConfirmDialog from '@/components/ui/ConfirmDialog.svelte';
    import LanguageSwitcher from '@/components/ui/LanguageSwitcher.svelte';
    import SeoHead from '@/components/SeoHead.svelte';
    import ThemeToggle from '@/components/ui/ThemeToggle.svelte';
    import { t } from '@/lib/i18n';
    import { Link, page, router } from '@inertiajs/svelte';
    import {
        Activity,
        ArrowLeft,
        FolderTree,
        LayoutDashboard,
        LogOut,
        Menu,
        Users,
        X,
    } from '@lucide/svelte';
    import type { Snippet } from 'svelte';

    interface Props {
        title?: string;
        children?: Snippet;
    }

    let {
        title = 'Panel Administrator',
        children,
    }: Props = $props();

    const appName = $derived(page.props.name || 'CatatDuit');
    const fullTitle = $derived(title ? `${title} - Admin ${appName}` : `Admin ${appName}`);

    const user = $derived(page.props.auth?.user);
    const flash = $derived(page.props.flash as { success?: string; error?: string } | undefined);
    const currentUrl = $derived(page.url);

    const adminNavItems = $derived([
        { label: t('admin.metrics'), href: '/admin', icon: LayoutDashboard, exact: true },
        { label: t('admin.users'), href: '/admin/users', icon: Users },
        { label: t('admin.categories'), href: '/admin/categories', icon: FolderTree },
        { label: t('admin.audit_logs'), href: '/admin/audit-logs', icon: Activity },
    ]);

    function isActive(itemHref: string, exact = false): boolean {
        if (exact) return currentUrl === itemHref;
        return currentUrl.startsWith(itemHref);
    }

    let isMobileMenuOpen = $state(false);

    function toggleMobileMenu() {
        isMobileMenuOpen = !isMobileMenuOpen;
    }

    function closeMobileMenu() {
        isMobileMenuOpen = false;
    }

    // Auto-close mobile drawer on route change
    $effect(() => {
        const _ = currentUrl;
        isMobileMenuOpen = false;
    });

    function handleKeydown(e: KeyboardEvent) {
        if (e.key === 'Escape' && isMobileMenuOpen) {
            isMobileMenuOpen = false;
        }
    }

    let isLogoutConfirmOpen = $state(false);
    let isLoggingOut = $state(false);

    function handleLogoutClick() {
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
</script>

<SeoHead {title} noindex={true} />

<svelte:window onkeydown={handleKeydown} />

<div class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex">
    <!-- Desktop Left Sidebar Rail -->
    <aside class="w-64 bg-[#002B54] text-white shrink-0 hidden md:flex flex-col justify-between p-5 border-r border-[#003A70]/60 sticky top-0 h-screen h-dvh self-start overflow-y-auto z-30">
        <div>
            <!-- Admin Brand -->
            <div class="flex items-center gap-3 mb-8 px-2">
                <img
                    src="/favicon.ico"
                    alt={appName}
                    class="w-9 h-9 rounded-xl bg-white p-1 object-contain shadow-md shadow-[#FFB700]/20"
                />
                <div>
                    <h3 class="font-bold text-sm tracking-tight text-white">{appName}</h3>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-[#FFD200]">
                        {t('admin.portal')}
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1">
                {#each adminNavItems as item}
                    {@const active = isActive(item.href, item.exact)}
                    <Link
                        href={item.href}
                        class={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all ${
                            active
                                ? 'bg-[#FFB700] text-[#003A70] font-bold shadow-sm shadow-[#FFB700]/20'
                                : 'text-slate-300 hover:text-white hover:bg-[#003A70]'
                        }`}
                    >
                        <item.icon class="w-4 h-4" />
                        <span>{item.label}</span>
                    </Link>
                {/each}
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="pt-4 border-t border-[#003A70]/60 space-y-2">
            <Link
                href="/app"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-[#003A70] transition-colors"
            >
                <ArrowLeft class="w-4 h-4" />
                {t('admin.back_to_app')}
            </Link>

            <button
                type="button"
                onclick={handleLogoutClick}
                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-rose-300 hover:text-rose-200 hover:bg-[#003A70] transition-colors cursor-pointer text-left"
            >
                <LogOut class="w-4 h-4" />
                {t('auth.logout')}
            </button>
        </div>
    </aside>

    <!-- Mobile Slide-over Drawer & Overlay -->
    {#if isMobileMenuOpen}
        <button
            type="button"
            aria-label="Tutup menu navigasi"
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs md:hidden cursor-default w-full h-full border-none p-0"
            onclick={closeMobileMenu}
        ></button>
    {/if}

    <aside
        class={`fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-[#002B54] text-white flex flex-col justify-between p-5 shadow-2xl transition-transform duration-300 ease-in-out md:hidden ${
            isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
    >
        <div>
            <!-- Mobile Drawer Header -->
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#003A70]/60">
                <div class="flex items-center gap-3">
                    <img
                        src="/favicon.ico"
                        alt={appName}
                        class="w-9 h-9 rounded-xl bg-white p-1 object-contain shadow-md shadow-[#FFB700]/20"
                    />
                    <div>
                        <h3 class="font-bold text-sm tracking-tight text-white">{appName}</h3>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-[#FFD200]">
                            {t('admin.portal')}
                        </span>
                    </div>
                </div>
                <button
                    type="button"
                    onclick={closeMobileMenu}
                    class="p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-[#003A70] transition-colors cursor-pointer"
                    aria-label="Close menu"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Administrator Profile Card -->
            <div class="p-3 rounded-xl bg-[#003A70]/60 border border-[#003A70] flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-full bg-[#002B54] text-[#FFB700] font-bold text-xs flex items-center justify-center border border-[#FFB700]/40 shadow-xs shrink-0">
                    {user?.name ? user.name.charAt(0).toUpperCase() : 'A'}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-xs text-white truncate">{user?.name}</p>
                    <p class="text-[11px] text-slate-300 truncate">{user?.email}</p>
                </div>
                <span class="text-[9px] uppercase font-bold tracking-wider text-[#003A70] bg-[#FFD200] px-1.5 py-0.5 rounded font-mono shrink-0">
                    Admin
                </span>
            </div>

            <!-- Mobile Navigation Links -->
            <nav class="space-y-1">
                {#each adminNavItems as item}
                    {@const active = isActive(item.href, item.exact)}
                    <Link
                        href={item.href}
                        class={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all ${
                            active
                                ? 'bg-[#FFB700] text-[#003A70] font-bold shadow-sm shadow-[#FFB700]/20'
                                : 'text-slate-300 hover:text-white hover:bg-[#003A70]'
                        }`}
                    >
                        <item.icon class="w-4 h-4" />
                        <span>{item.label}</span>
                    </Link>
                {/each}
            </nav>
        </div>

        <!-- Mobile Drawer Footer & Settings -->
        <div class="pt-4 border-t border-[#003A70]/60 space-y-3">
            <!-- Preferences in Drawer -->
            <div class="space-y-2 py-1 bg-[#003A70]/30 p-2.5 rounded-xl border border-[#003A70]/50">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-300 font-medium">{t('theme.title')}</span>
                    <ThemeToggle />
                </div>
                <div class="flex items-center justify-between text-xs pt-1 border-t border-[#003A70]/40">
                    <span class="text-slate-300 font-medium">{t('common.language')}</span>
                    <LanguageSwitcher compact />
                </div>
            </div>

            <div class="space-y-1 pt-1">
                <Link
                    href="/app"
                    class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-[#003A70] transition-colors"
                >
                    <ArrowLeft class="w-4 h-4" />
                    {t('admin.back_to_app')}
                </Link>

                <button
                    type="button"
                    onclick={() => {
                        closeMobileMenu();
                        handleLogoutClick();
                    }}
                    class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-rose-300 hover:text-rose-200 hover:bg-[#003A70] transition-colors cursor-pointer text-left"
                >
                    <LogOut class="w-4 h-4" />
                    {t('auth.logout')}
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Bar -->
        <header class="sticky top-0 z-20 h-16 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 px-3 sm:px-6 lg:px-8 flex items-center justify-between shrink-0">
            <!-- Left Side: Hamburger & Clean Title -->
            <div class="flex items-center gap-2 sm:gap-3 min-w-0 pr-2">
                <button
                    type="button"
                    onclick={toggleMobileMenu}
                    class="md:hidden p-2 -ml-1 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none cursor-pointer shrink-0"
                    aria-label="Toggle mobile navigation menu"
                >
                    <Menu class="w-5 h-5" />
                </button>

                <div class="flex items-center gap-2 min-w-0">
                    <img src="/favicon.ico" alt="" class="w-6 h-6 rounded-lg bg-white p-0.5 object-contain shadow-xs shadow-[#FFB700]/20 shrink-0" />
                    <!-- Mobile Current Page Title -->
                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate md:hidden">
                        {title}
                    </span>
                    <!-- Desktop Header Title -->
                    <h1 class="text-base font-bold text-slate-900 dark:text-slate-100 hidden md:block truncate">
                        {title}
                    </h1>
                </div>
            </div>

            <!-- Right Side Controls -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <!-- Mobile 1-Tap Language Toggle -->
                <div class="sm:hidden">
                    <LanguageSwitcher variant="toggle" />
                </div>

                <!-- Desktop Full Language Select Dropdown -->
                <div class="hidden sm:block">
                    <LanguageSwitcher compact />
                </div>

                <!-- Theme Toggle -->
                <ThemeToggle />

                <!-- User Profile Label (Desktop) -->
                <div class="text-xs text-right hidden sm:block">
                    <p class="font-bold text-slate-900 dark:text-slate-100 truncate max-w-[140px]">{user?.name}</p>
                    <p class="text-slate-500 dark:text-slate-400 text-[11px] truncate max-w-[140px]">{user?.email}</p>
                </div>
            </div>
        </header>

        <!-- Flash Notice -->
        {#if flash?.success}
            <div class="px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                    {flash.success}
                </div>
            </div>
        {/if}

        {#if flash?.error}
            <div class="px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-semibold text-rose-800 dark:text-rose-300">
                    {flash.error}
                </div>
            </div>
        {/if}

        <!-- Page Viewport -->
        <main class="flex-1 p-3.5 sm:p-6 lg:p-8">
            {@render children?.()}
        </main>
    </div>

    <!-- Admin Logout Confirmation Dialog -->
    <ConfirmDialog
        bind:open={isLogoutConfirmOpen}
        title={t('auth.logout_admin_title')}
        message={t('auth.logout_admin_message')}
        confirmText={t('auth.logout')}
        cancelText={t('common.cancel')}
        variant="danger"
        iconType="logout"
        loading={isLoggingOut}
        onconfirm={confirmLogout}
    />
</div>
