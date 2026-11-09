<script lang="ts">
    import Button from '@/components/ui/Button.svelte';
    import Input from '@/components/ui/Input.svelte';
    import GuestLayout from '@/layouts/GuestLayout.svelte';
    import { t } from '@/lib/i18n';
    import { createResendCooldown } from '@/lib/resendCooldown.svelte';
    import { Link, useForm } from '@inertiajs/svelte';
    import { ArrowLeft, CircleCheck, KeyRound, Mail, Pencil, RefreshCw } from '@lucide/svelte';
    import { onMount } from 'svelte';

    interface Props {
        status?: string;
        cooldownSeconds?: number;
        submittedEmail?: string;
    }

    const STORAGE_KEY_EMAIL = 'cd_pwd_reset_email';

    let { status, cooldownSeconds = 0, submittedEmail: serverEmail = '' }: Props = $props();

    const form = useForm({
        email: '',
    });

    let submittedEmail = $state('');
    let isEditingEmail = $state(false);
    const cooldown = createResendCooldown();

    onMount(() => {
        // Restore submitted email from server prop or sessionStorage
        try {
            const storedEmail = sessionStorage.getItem(STORAGE_KEY_EMAIL);
            if (serverEmail) {
                submittedEmail = serverEmail;
                sessionStorage.setItem(STORAGE_KEY_EMAIL, serverEmail);
            } else if (storedEmail) {
                submittedEmail = storedEmail;
            }
        } catch {}

        function handleVisibilityChange() {
            if (!document.hidden) {
                cooldown.update();
            }
        }

        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('focus', handleVisibilityChange);

        return () => {
            document.removeEventListener('visibilitychange', handleVisibilityChange);
            window.removeEventListener('focus', handleVisibilityChange);
        };
    });

    let previousCooldown: number | null = null;
    $effect(() => {
        const current = cooldownSeconds;
        if (current !== previousCooldown) {
            previousCooldown = current;
            cooldown.syncFromServer(current);
        }

        if (serverEmail && !submittedEmail) {
            submittedEmail = serverEmail;
        }
    });

    function handleSubmit(e: SubmitEvent) {
        e.preventDefault();
        submittedEmail = form.email;
        try {
            sessionStorage.setItem(STORAGE_KEY_EMAIL, form.email);
        } catch {}

        form.post('/forgot-password', {
            onSuccess: () => {
                isEditingEmail = false;
                cooldown.start(60);
            },
        });
    }

    function handleResend() {
        if (cooldown.state.secondsLeft > 0 || form.processing) return;
        const target = submittedEmail || form.email;
        if (!target) return;

        form.email = target;
        form.post('/forgot-password', {
            onSuccess: () => {
                cooldown.start(60);
            },
        });
    }

    const isConfirmationVisible = $derived((status || cooldown.state.secondsLeft > 0) && !isEditingEmail);
</script>

<GuestLayout
    title={t('auth.forgot_title')}
    description="Atur ulang kata sandi akun CatatDuit Anda dengan tautan verifikasi yang aman."
>
    <div class="space-y-5">
        <div>
            <div class="w-10 h-10 rounded-xl bg-[#003A70]/10 dark:bg-[#003A70]/30 text-[#003A70] dark:text-[#FFB700] flex items-center justify-center mb-3">
                <KeyRound class="w-5 h-5" />
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                {t('auth.reset_password')}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                {t('auth.reset_description')}
            </p>
        </div>

        {#if isConfirmationVisible}
            <!-- Success Confirmation Card -->
            <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/70 text-slate-900 dark:text-slate-100 space-y-3 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                        <CircleCheck class="w-5 h-5" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="font-bold text-sm text-emerald-900 dark:text-emerald-200">
                            {t('auth.email_reset_sent')}
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            {t('auth.reset_sent_to')}
                        </p>
                        <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300 break-all">
                            {submittedEmail || form.email}
                        </p>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 dark:text-slate-400 pt-2 border-t border-emerald-200/60 dark:border-emerald-800/40 leading-relaxed">
                    {t('auth.check_inbox')}
                </p>
            </div>

            <!-- Resend Actions Section with Anti-Spam Timer -->
            <div class="space-y-3 pt-1">
                {#if cooldown.state.secondsLeft > 0}
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400">
                        {t('auth.resend_in')} <strong class="font-bold text-[#003A70] dark:text-[#FFD200]">{cooldown.state.secondsLeft} detik</strong>
                    </div>
                    <Button
                        type="button"
                        variant="secondary"
                        class="w-full h-11 rounded-xl text-xs font-semibold opacity-60 cursor-not-allowed select-none"
                        disabled
                    >
                        <RefreshCw class="w-3.5 h-3.5 mr-1.5" />
                        {t('auth.resend_link')} ({cooldown.state.secondsLeft}s)
                    </Button>
                {:else}
                    <Button
                        type="button"
                        variant="primary"
                        class="w-full h-11 rounded-xl text-sm font-semibold shadow-sm hover:shadow-md transition-all cursor-pointer"
                        loading={form.processing}
                        onclick={handleResend}
                    >
                        <RefreshCw class="w-4 h-4 mr-1.5" />
                        {t('auth.resend_link')}
                    </Button>
                {/if}

                <div class="text-center pt-1">
                    <button
                        type="button"
                        onclick={() => {
                            isEditingEmail = true;
                            form.email = submittedEmail || form.email;
                        }}
                        class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 inline-flex items-center gap-1 cursor-pointer transition-colors"
                    >
                        <Pencil class="w-3 h-3" />
                        {t('auth.change_email')}
                    </button>
                </div>
            </div>
        {:else}
            <form onsubmit={handleSubmit} class="space-y-4">
                <Input
                    label={t('auth.email')}
                    type="email"
                    placeholder="nama@email.com"
                    required
                    autocomplete="email"
                    bind:value={form.email}
                    error={form.errors.email}
                >
                    {#snippet leadingIcon()}
                        <Mail class="w-4 h-4" />
                    {/snippet}
                </Input>

                <Button
                    type="submit"
                    variant="primary"
                    class="w-full h-11 rounded-xl text-sm font-semibold shadow-sm hover:shadow-md transition-all mt-2 cursor-pointer"
                    loading={form.processing}
                    disabled={cooldown.state.secondsLeft > 0}
                >
                    {#if cooldown.state.secondsLeft > 0}
                        {t('auth.wait_seconds', { seconds: cooldown.state.secondsLeft })}
                    {:else if isEditingEmail}
                        {t('auth.send_new_link')}
                    {:else}
                        {t('auth.send_reset_link')}
                    {/if}
                </Button>

                {#if isEditingEmail}
                    <div class="text-center pt-1">
                        <button
                            type="button"
                            onclick={() => { isEditingEmail = false; }}
                            class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 cursor-pointer"
                        >
                            {t('auth.cancel_change_email')}
                        </button>
                    </div>
                {/if}
            </form>
        {/if}

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 text-center">
            <Link
                href="/login"
                class="text-xs sm:text-sm font-semibold text-[#003A70] hover:text-[#0C3B67] dark:text-[#FFD200] dark:hover:text-[#FFB700] transition-colors inline-flex items-center justify-center gap-1.5"
            >
                <ArrowLeft class="w-3.5 h-3.5" />
                <span>{t('auth.back_to_login')}</span>
            </Link>
        </div>
    </div>
</GuestLayout>
