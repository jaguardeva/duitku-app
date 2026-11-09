<script lang="ts">
    import Badge from '@/components/ui/Badge.svelte';
    import Button from '@/components/ui/Button.svelte';
    import Card from '@/components/ui/Card.svelte';
    import Input from '@/components/ui/Input.svelte';
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import { t } from '@/lib/i18n';
    import { Link, page, router } from '@inertiajs/svelte';
    import {
        Activity,
        AlertOctagon,
        CheckCircle2,
        ChevronRight,
        Clock,
        FolderTree,
        ShieldAlert,
        ShieldCheck,
        Tag,
        TrendingUp,
        UserCheck,
        UserPlus,
        Users,
    } from '@lucide/svelte';

    interface AuditLogSummary {
        id: string;
        action: string;
        ip_address?: string;
        created_at: string;
        admin?: { name: string; email: string };
    }

    interface Props {
        metrics?: {
            total_users: number;
            active_users: number;
            suspended_users: number;
            verified_users: number;
            unverified_users: number;
            new_registrations_30d: number;
            system_categories: number;
            income_categories?: number;
            expense_categories?: number;
            total_audit_logs?: number;
        };
        recentAuditLogs?: AuditLogSummary[];
    }

    let {
        metrics = {
            total_users: 0,
            active_users: 0,
            suspended_users: 0,
            verified_users: 0,
            unverified_users: 0,
            new_registrations_30d: 0,
            system_categories: 0,
            income_categories: 0,
            expense_categories: 0,
            total_audit_logs: 0,
        },
        recentAuditLogs = [],
    }: Props = $props();

    const currentVersion = $derived((page.props.app_version as string) || '1.0.0');

    let newVersion = $state('');
    let isSaving = $state(false);
    let versionError = $state<string | undefined>(undefined);

    $effect(() => {
        if (!newVersion) {
            newVersion = currentVersion;
        }
    });

    function handleUpdateVersion(e: SubmitEvent) {
        e.preventDefault();
        versionError = undefined;

        if (!newVersion.trim()) {
            versionError = t('common.required');
            return;
        }

        isSaving = true;
        router.put('/admin/version', { version: newVersion.trim() }, {
            preserveScroll: true,
            onError: (errors) => {
                if (errors.version) {
                    versionError = errors.version;
                }
            },
            onFinish: () => {
                isSaving = false;
            },
        });
    }

    // Calculated percentage ratios for visual indicators
    const activeRate = $derived(
        metrics.total_users > 0
            ? Math.round((metrics.active_users / metrics.total_users) * 100)
            : 0
    );

    const suspendedRate = $derived(
        metrics.total_users > 0
            ? Math.round((metrics.suspended_users / metrics.total_users) * 100)
            : 0
    );

    const verifiedRate = $derived(
        metrics.total_users > 0
            ? Math.round((metrics.verified_users / metrics.total_users) * 100)
            : 0
    );
</script>

<AdminLayout title={t('admin.metrics')}>
    <div class="space-y-6">
        <!-- Dashboard Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 pb-1 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-slate-100">
                    {t('admin_page.operational_summary')}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Monitoring operasional real-time ekosistem pengguna, integritas data, dan tata kelola platform CatatDuit.
                </p>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/80 text-emerald-700 dark:text-emerald-400 text-xs font-semibold shadow-2xs self-start sm:self-auto shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{t('admin_page.system_status_normal')}</span>
            </div>
        </div>

        <!-- Main Strategic Grid (2 Consolidated Cards) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">
            <!-- Card 1: Ekosistem & Pertumbuhan Pengguna (Consolidated 5 User Metrics) -->
            <Card class="lg:col-span-7 p-4 sm:p-6 space-y-5 sm:space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#003A70]/10 dark:bg-[#003A70]/30 text-[#003A70] dark:text-[#FFB700] flex items-center justify-center shrink-0">
                            <Users class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                {t('admin_page.user_ecosystem')}
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {t('admin_page.user_health_desc')}
                            </p>
                        </div>
                    </div>
                    <Link
                        href="/admin/users"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-[#003A70] hover:text-[#002B54] dark:text-[#FFB700] dark:hover:text-[#FFD200] transition-colors"
                    >
                        <span>{t('admin_page.view_all_users')}</span>
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                </div>

                <!-- Hero KPI: Total Users & Growth -->
                <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-3 bg-slate-50/70 dark:bg-slate-800/40 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            {t('admin_page.total_users')}
                        </span>
                        <div class="text-2xl sm:text-4xl font-extrabold font-mono text-slate-900 dark:text-slate-100 mt-1">
                            {metrics.total_users.toLocaleString()}
                        </div>
                    </div>

                    {#if metrics.new_registrations_30d > 0}
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 self-start sm:self-auto">
                            <TrendingUp class="w-3.5 h-3.5" />
                            <span>+{metrics.new_registrations_30d} {t('admin_page.new_registrations')}</span>
                        </div>
                    {/if}
                </div>

                <!-- Visual Ratio Bar (Active vs Suspended) -->
                <div class="space-y-1.5">
                    <div class="flex justify-between text-xs font-medium text-slate-600 dark:text-slate-400">
                        <span>Rasio Kesehatan Akun</span>
                        <span class="font-mono text-xs">{activeRate}% Aktif</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden flex">
                        <div
                            class="bg-emerald-500 transition-all duration-500"
                            style={`width: ${activeRate}%`}
                        ></div>
                        <div
                            class="bg-rose-500 transition-all duration-500"
                            style={`width: ${suspendedRate}%`}
                        ></div>
                    </div>
                </div>

                <!-- 2x2 Sub-metrics Grid (Consolidated Info) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 pt-1">
                    <!-- Pengguna Aktif -->
                    <Link
                        href="/admin/users?status=active"
                        class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-700 bg-white dark:bg-slate-900/60 hover:bg-emerald-50/20 dark:hover:bg-emerald-950/10 transition-colors group"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                                {t('admin_page.active_users')}
                            </span>
                            <UserCheck class="w-4 h-4 text-emerald-500" />
                        </div>
                        <div class="mt-2 flex items-baseline justify-between">
                            <span class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400">
                                {metrics.active_users.toLocaleString()}
                            </span>
                            <span class="text-[11px] font-semibold text-slate-400 group-hover:text-emerald-600 transition-colors">
                                {activeRate}%
                            </span>
                        </div>
                    </Link>

                    <!-- Akun Ditangguhkan -->
                    <Link
                        href="/admin/users?status=suspended"
                        class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-rose-300 dark:hover:border-rose-700 bg-white dark:bg-slate-900/60 hover:bg-rose-50/20 dark:hover:bg-rose-950/10 transition-colors group"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                                {t('admin_page.suspended_users')}
                            </span>
                            <AlertOctagon class="w-4 h-4 text-rose-500" />
                        </div>
                        <div class="mt-2 flex items-baseline justify-between">
                            <span class="text-2xl font-bold font-mono text-rose-600 dark:text-rose-400">
                                {metrics.suspended_users.toLocaleString()}
                            </span>
                            <span class="text-[11px] font-semibold text-slate-400 group-hover:text-rose-600 transition-colors">
                                {suspendedRate}%
                            </span>
                        </div>
                    </Link>

                    <!-- Email Terverifikasi -->
                    <Link
                        href="/admin/users?verification=verified"
                        class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-700 bg-white dark:bg-slate-900/60 hover:bg-indigo-50/20 dark:hover:bg-indigo-950/10 transition-colors group"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                                {t('admin_page.verified_users')}
                            </span>
                            <CheckCircle2 class="w-4 h-4 text-indigo-500" />
                        </div>
                        <div class="mt-2 flex items-baseline justify-between">
                            <span class="text-2xl font-bold font-mono text-indigo-600 dark:text-indigo-400">
                                {metrics.verified_users.toLocaleString()}
                            </span>
                            <span class="text-[11px] font-semibold text-slate-400 group-hover:text-indigo-600 transition-colors">
                                {verifiedRate}%
                            </span>
                        </div>
                    </Link>

                    <!-- Belum Verifikasi -->
                    <Link
                        href="/admin/users?verification=unverified"
                        class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-amber-300 dark:hover:border-amber-700 bg-white dark:bg-slate-900/60 hover:bg-amber-50/20 dark:hover:bg-amber-950/10 transition-colors group"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                                {t('admin_page.not_verified')}
                            </span>
                            <ShieldAlert class="w-4 h-4 text-amber-500" />
                        </div>
                        <div class="mt-2 flex items-baseline justify-between">
                            <span class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400">
                                {metrics.unverified_users.toLocaleString()}
                            </span>
                            <span class="text-[11px] font-semibold text-slate-400 group-hover:text-amber-600 transition-colors">
                                {100 - verifiedRate}%
                            </span>
                        </div>
                    </Link>
                </div>
            </Card>

            <!-- Card 2: Tata Kelola Platform & Sumber Daya Sistem (Categories + Version Management) -->
            <Card class="lg:col-span-5 p-4 sm:p-6 space-y-5 sm:space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center shrink-0">
                            <FolderTree class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                {t('admin_page.platform_governance')}
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {t('admin_page.categories_desc')}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section A: Kategori Global Sistem -->
                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                {t('admin_page.system_categories')}
                            </span>
                            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-slate-100 mt-0.5">
                                {metrics.system_categories} Kategori
                            </div>
                        </div>
                        <Link href="/admin/categories">
                            <Button variant="outline" size="sm" class="text-xs self-start sm:self-auto">
                                {t('admin_page.view_all_categories')}
                                <ChevronRight class="w-3.5 h-3.5 ml-1" />
                            </Button>
                        </Link>
                    </div>

                    <!-- Income vs Expense Sub-pills -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div class="px-3 py-2 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-between">
                            <span class="text-[11px] font-medium text-emerald-700 dark:text-emerald-400">Pemasukan</span>
                            <span class="text-xs font-bold font-mono text-emerald-700 dark:text-emerald-300">
                                {metrics.income_categories ?? 0}
                            </span>
                        </div>
                        <div class="px-3 py-2 rounded-lg bg-rose-50/60 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 flex items-center justify-between">
                            <span class="text-[11px] font-medium text-rose-700 dark:text-rose-400">Pengeluaran</span>
                            <span class="text-xs font-bold font-mono text-rose-700 dark:text-rose-300">
                                {metrics.expense_categories ?? 0}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800"></div>

                <!-- Section B: Pengaturan Versi Aplikasi -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <Tag class="w-4 h-4 text-[#003A70] dark:text-[#FFB700]" />
                            <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100">
                                {t('admin.version_management')}
                            </h4>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-slate-400">{t('admin.current_version')}:</span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold bg-[#FFB700]/20 text-[#8A6100] dark:text-[#FFD200] border border-[#FFB700]/40">
                                v{currentVersion}
                            </span>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        {t('admin.version_management_desc')}
                    </p>

                    <form onsubmit={handleUpdateVersion} class="pt-1 flex flex-col sm:flex-row items-stretch sm:items-end gap-2">
                        <div class="flex-1">
                            <Input
                                placeholder={t('admin.version_placeholder')}
                                bind:value={newVersion}
                                error={versionError}
                                disabled={isSaving}
                            />
                        </div>
                        <Button
                            type="submit"
                            variant="primary"
                            size="sm"
                            loading={isSaving}
                            disabled={isSaving || !newVersion.trim() || newVersion.trim() === currentVersion}
                            class="h-10 text-xs shrink-0"
                        >
                            {isSaving ? t('admin.updating_version') : t('admin.update_version')}
                        </Button>
                    </form>
                </div>
            </Card>
        </div>

        <!-- Bottom Grid: Recent Audit Logs & Privacy Compliance Notice -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">
            <!-- Left: Aktivitas Audit Log Terkini (7 Cols) -->
            <Card class="lg:col-span-7 p-4 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <Activity class="w-4 h-4 text-[#003A70] dark:text-[#FFB700]" />
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                            {t('admin_page.recent_audit_logs')}
                        </h3>
                    </div>
                    <Link
                        href="/admin/audit-logs"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-[#003A70] hover:text-[#002B54] dark:text-[#FFB700] dark:hover:text-[#FFD200] transition-colors"
                    >
                        <span>{t('admin_page.view_all_logs')}</span>
                        <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    {#each recentAuditLogs as log (log.id)}
                        <div class="py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-3 text-xs">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <Badge variant="primary" size="sm" class="font-mono text-[10px] px-1.5 py-0.5">
                                        {log.action}
                                    </Badge>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 truncate">
                                        {log.admin?.name || 'Sistem'}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 font-mono">
                                    IP: {log.ip_address || '-'}
                                </div>
                            </div>
                            <div class="shrink-0 font-mono text-[11px] text-slate-400 self-start sm:self-auto">
                                {log.created_at.replace('T', ' ').substring(0, 16)}
                            </div>
                        </div>
                    {:else}
                        <div class="py-6 text-center text-xs text-slate-400">
                            Belum ada aktivitas audit log tercatat.
                        </div>
                    {/each}
                </div>
            </Card>

            <!-- Right: Ketentuan Privasi & Keamanan Data (5 Cols) -->
            <Card class="lg:col-span-5 p-4 sm:p-6 border-l-4 border-l-amber-500 bg-amber-50/30 dark:bg-amber-950/20 space-y-3">
                <div class="flex items-center gap-2">
                    <ShieldCheck class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" />
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                        {t('admin_page.privacy_title')}
                    </h3>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    {t('admin_page.privacy_description')}
                </p>

                <div class="pt-2 border-t border-amber-200/50 dark:border-amber-800/40 text-[11px] text-slate-500 dark:text-slate-400 space-y-1">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Transaksi personal & saldo pengguna terlindungi privasi</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        <span>Seluruh aksi administrator dicatat permanen dalam audit log</span>
                    </div>
                </div>
            </Card>
        </div>
    </div>
</AdminLayout>

