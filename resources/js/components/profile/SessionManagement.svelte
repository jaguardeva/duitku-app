<script lang="ts">
    import Badge from '@/components/ui/Badge.svelte';
    import Button from '@/components/ui/Button.svelte';
    import Card from '@/components/ui/Card.svelte';
    import ConfirmDialog from '@/components/ui/ConfirmDialog.svelte';
    import PasswordInput from '@/components/ui/PasswordInput.svelte';
    import { destroy, destroyAll, destroyOthers } from '@/routes/profile/sessions';
    import type { SessionItem } from '@/types';
    import { router, useForm } from '@inertiajs/svelte';
    import { t } from '@/lib/i18n';
    import {
        AlertTriangle,
        Clock,
        Globe,
        Laptop,
        LogOut,
        ShieldAlert,
        Smartphone,
        Tablet,
        X,
    } from '@lucide/svelte';

    interface Props {
        sessions?: SessionItem[];
    }

    let { sessions = [] }: Props = $props();

    const currentSession = $derived(sessions.find((session) => session.is_current_device));
    const otherSessions = $derived(sessions.filter((session) => !session.is_current_device));

    let sessionToRevoke = $state<SessionItem | null>(null);
    let isRevokingSingle = $state(false);
    let isLogoutOthersModalOpen = $state(false);
    let isLogoutAllModalOpen = $state(false);

    const logoutOthersForm = useForm({ password: '' });
    const logoutAllForm = useForm({ password: '' });

    function getDeviceIcon(deviceType: SessionItem['agent']['device_type']) {
        if (deviceType === 'mobile') return Smartphone;
        if (deviceType === 'tablet') return Tablet;

        return Laptop;
    }

    function confirmRevokeSingle() {
        if (!sessionToRevoke) return;

        isRevokingSingle = true;
        router.delete(destroy(sessionToRevoke.id).url, {
            preserveScroll: true,
            onFinish: () => {
                isRevokingSingle = false;
                sessionToRevoke = null;
            },
        });
    }

    function handleLogoutOthersSubmit(event: SubmitEvent) {
        event.preventDefault();
        logoutOthersForm.post(destroyOthers.url(), {
            preserveScroll: true,
            onSuccess: () => {
                isLogoutOthersModalOpen = false;
                logoutOthersForm.reset();
            },
        });
    }

    function handleLogoutAllSubmit(event: SubmitEvent) {
        event.preventDefault();
        logoutAllForm.post(destroyAll.url(), {
            onSuccess: () => {
                isLogoutAllModalOpen = false;
                logoutAllForm.reset();
            },
        });
    }

    function handleModalKeydown(event: KeyboardEvent) {
        if (event.key !== 'Escape') return;

        if (isLogoutOthersModalOpen && !logoutOthersForm.processing) {
            isLogoutOthersModalOpen = false;
            logoutOthersForm.reset();
        }

        if (isLogoutAllModalOpen && !logoutAllForm.processing) {
            isLogoutAllModalOpen = false;
            logoutAllForm.reset();
        }
    }
</script>

<svelte:window onkeydown={handleModalKeydown} />

<section class="space-y-4" aria-labelledby="session-management-title">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{t('sessions.access')}</p>
            <h2 id="session-management-title" class="mt-1 text-base font-bold text-slate-900 dark:text-slate-100">{t('sessions.title')}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{t('sessions.description')}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            {#if otherSessions.length > 0}
                <Button
                    variant="outline"
                    size="sm"
                    onclick={() => {
                        logoutOthersForm.clearErrors();
                        logoutOthersForm.reset();
                        isLogoutOthersModalOpen = true;
                    }}
                >
                    <LogOut class="h-3.5 w-3.5" />
                    {t('sessions.revoke_other_title')}
                </Button>
            {/if}
            <Button
                variant="ghost"
                size="sm"
                class="text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40"
                onclick={() => {
                    logoutAllForm.clearErrors();
                    logoutAllForm.reset();
                    isLogoutAllModalOpen = true;
                }}
            >
                <ShieldAlert class="h-3.5 w-3.5" />
                {t('sessions.logout_all_title')}
            </Button>
        </div>
    </div>

    <Card class="overflow-hidden p-0">
        {#if currentSession}
            {@const CurrentIcon = getDeviceIcon(currentSession.agent.device_type)}
            <div class="flex flex-col gap-4 bg-[#003A70]/5 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5 dark:bg-[#FFB700]/5">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#003A70] text-white dark:bg-[#FFB700] dark:text-[#003A70]">
                        <CurrentIcon class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{currentSession.agent.display_name}</h3>
                            <Badge variant="accent" size="sm">{t('sessions.this_device')}</Badge>
                        </div>
                        <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                            <span>{currentSession.agent.platform}</span>
                            <span>{currentSession.agent.browser}</span>
                            <span class="font-mono">{currentSession.ip_address}</span>
                        </div>
                    </div>
                </div>
                <span class="shrink-0 text-xs font-medium text-emerald-600 dark:text-emerald-400">{t('sessions.now')}</span>
            </div>
        {/if}

        <div class="border-t border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between px-4 py-3 sm:px-5">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{t('sessions.other_devices')}</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">{otherSessions.length} sesi</span>
            </div>

            {#if otherSessions.length === 0}
                <div class="border-t border-slate-200 px-4 py-4 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400 sm:px-5">
                    {t('empty.no_other_sessions')}
                </div>
            {:else}
                {#each otherSessions as session (session.id)}
                    {@const SessionIcon = getDeviceIcon(session.agent.device_type)}
                    <div class="flex items-center gap-3 border-t border-slate-200 px-4 py-4 dark:border-slate-800 sm:px-5">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            <SessionIcon class="h-4 w-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">{session.agent.display_name}</h4>
                            <div class="mt-1 flex flex-wrap gap-x-2 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                <span>{session.agent.platform}</span>
                                <span>{session.agent.browser}</span>
                                <span class="font-mono">{session.ip_address}</span>
                                <span class="inline-flex items-center gap-1" title={session.last_active_at}><Clock class="h-3 w-3" />{session.last_active}</span>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2 py-1.5 text-xs font-semibold text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40"
                            onclick={() => (sessionToRevoke = session)}
                            aria-label={`${t('sessions.revoke')} ${session.agent.display_name}`}
                        >
                            <LogOut class="h-3.5 w-3.5" />
                            <span class="hidden sm:inline">{t('sessions.revoke')}</span>
                        </button>
                    </div>
                {/each}
            {/if}
        </div>
    </Card>
</section>

{#if sessionToRevoke}
    <ConfirmDialog
        open={true}
        title={t('sessions.revoke')}
        message={`${t('sessions.revoke')} ${sessionToRevoke.agent.display_name} (${sessionToRevoke.ip_address})?`}
        confirmText={t('sessions.revoke')}
        cancelText={t('common.cancel')}
        iconType="logout"
        loading={isRevokingSingle}
        onconfirm={confirmRevokeSingle}
        oncancel={() => (sessionToRevoke = null)}
    />
{/if}

{#if isLogoutOthersModalOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="logout-others-title">
        <div class="w-full max-w-md space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 id="logout-others-title" class="text-sm font-bold text-slate-900 dark:text-slate-100">{t('sessions.revoke_other_title')}</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{t('sessions.other_stays')}</p>
                </div>
                <button type="button" onclick={() => (isLogoutOthersModalOpen = false)} disabled={logoutOthersForm.processing} class="rounded-md p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Tutup"><X class="h-4 w-4" /></button>
            </div>
            <form onsubmit={handleLogoutOthersSubmit} class="space-y-4">
                <PasswordInput label={t('auth.password_confirmation')} placeholder={t('sessions.password_current')} required bind:value={logoutOthersForm.password} error={logoutOthersForm.errors.password} />
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <Button type="button" variant="secondary" disabled={logoutOthersForm.processing} onclick={() => (isLogoutOthersModalOpen = false)}>{t('common.cancel')}</Button>
                    <Button type="submit" variant="accent" loading={logoutOthersForm.processing}>{t('sessions.revoke_other_title')}</Button>
                </div>
            </form>
        </div>
    </div>
{/if}

{#if isLogoutAllModalOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="logout-all-title">
        <div class="w-full max-w-md space-y-5 rounded-xl border border-rose-200 bg-white p-6 shadow-2xl dark:border-rose-900/60 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 id="logout-all-title" class="text-sm font-bold text-slate-900 dark:text-slate-100">{t('sessions.logout_all_title')}</h3>
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{t('sessions.logout_all_description')}</p>
                </div>
                <button type="button" onclick={() => (isLogoutAllModalOpen = false)} disabled={logoutAllForm.processing} class="rounded-md p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Tutup"><X class="h-4 w-4" /></button>
            </div>
            <div class="flex items-start gap-2.5 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs leading-relaxed text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300">
                <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                <span>{t('sessions.logout_all_warning')}</span>
            </div>
            <form onsubmit={handleLogoutAllSubmit} class="space-y-4">
                <PasswordInput label={t('auth.password_confirmation')} placeholder={t('sessions.password_confirm')} required bind:value={logoutAllForm.password} error={logoutAllForm.errors.password} />
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <Button type="button" variant="secondary" disabled={logoutAllForm.processing} onclick={() => (isLogoutAllModalOpen = false)}>{t('common.cancel')}</Button>
                    <Button type="submit" variant="destructive" loading={logoutAllForm.processing}>{t('sessions.logout_all_title')}</Button>
                </div>
            </form>
        </div>
    </div>
{/if}
