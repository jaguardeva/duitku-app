<script lang="ts">
    import Button from '@/components/ui/Button.svelte';
    import GuestLayout from '@/layouts/GuestLayout.svelte';
    import { t } from '@/lib/i18n';
    import { createResendCooldown } from '@/lib/resendCooldown.svelte';
    import { send } from '@/routes/verification';
    import { router, useForm } from '@inertiajs/svelte';
    import { MailCheck, RefreshCw } from '@lucide/svelte';
    import { onMount } from 'svelte';

    interface Props {
        email?: string;
        cooldownUntil?: number | null;
    }

    let { email = '', cooldownUntil = null }: Props = $props();

    const form = useForm({
        code: '',
    });

    const OTP_LENGTH = 6;
    let otpDigits = $state<string[]>(Array.from({ length: OTP_LENGTH }, () => ''));
    let otpInputElements = $state<Array<HTMLInputElement | undefined>>(
        Array.from({ length: OTP_LENGTH }, () => undefined),
    );

    const cooldown = createResendCooldown();
    let isResending = $state(false);
    let resendError = $state('');

    onMount(() => {
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
        const current = cooldownUntil;
        if (current !== previousCooldown) {
            previousCooldown = current;
            cooldown.syncFromServer(current);
        }
    });

    function syncOtpCode() {
        form.code = otpDigits.join('');
    }

    function focusOtpInput(index: number) {
        const input = otpInputElements[index];

        if (input) {
            input.focus();
            input.select();
        }
    }

    function fillOtpDigits(value: string, startIndex = 0) {
        const digits = value.replace(/\D/g, '').slice(0, OTP_LENGTH - startIndex);

        if (!digits) return;

        digits.split('').forEach((digit, offset) => {
            otpDigits[startIndex + offset] = digit;
        });

        syncOtpCode();
        focusOtpInput(Math.min(startIndex + digits.length, OTP_LENGTH - 1));
    }

    function handleOtpInput(index: number, event: Event) {
        const input = event.currentTarget as HTMLInputElement;
        const value = input.value.replace(/\D/g, '');

        if (!value) {
            otpDigits[index] = '';
            syncOtpCode();
            return;
        }

        fillOtpDigits(value, index);
    }

    function handleOtpKeydown(index: number, event: KeyboardEvent) {
        if (event.key === 'Backspace' && !otpDigits[index] && index > 0) {
            event.preventDefault();
            otpDigits[index - 1] = '';
            syncOtpCode();
            focusOtpInput(index - 1);
        }

        if (event.key === 'ArrowLeft' && index > 0) {
            event.preventDefault();
            focusOtpInput(index - 1);
        }

        if (event.key === 'ArrowRight' && index < OTP_LENGTH - 1) {
            event.preventDefault();
            focusOtpInput(index + 1);
        }
    }

    function handleOtpPaste(index: number, event: ClipboardEvent) {
        event.preventDefault();
        fillOtpDigits(event.clipboardData?.getData('text') ?? '', index);
    }

    function handleSubmit(e: SubmitEvent) {
        e.preventDefault();
        form.post('/email/verify-otp');
    }

    function handleResend() {
        if (cooldown.state.secondsLeft > 0 || isResending) return;

        isResending = true;
        resendError = '';

        router.post(send().url, {}, {
            preserveScroll: true,
            onError: (errors) => {
                resendError = errors.otp || errors.email || t('errors.request_failed');
            },
            onFinish: () => {
                isResending = false;
            },
        });
    }

    function handleLogout() {
        router.post('/logout');
    }
</script>

<GuestLayout title={t('auth.verify_email')} noindex={true}>
    <div class="space-y-5">
        <div>
            <div class="w-10 h-10 rounded-xl bg-[#003A70]/10 dark:bg-[#003A70]/30 text-[#003A70] dark:text-[#FFB700] flex items-center justify-center mb-3">
                <MailCheck class="w-5 h-5" />
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                {t('auth.verify_email')}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                {t('auth.otp_sent_to')}
                <strong class="font-semibold text-slate-800 dark:text-slate-200 block mt-0.5">{email}</strong>
            </p>
        </div>

        <form onsubmit={handleSubmit} class="space-y-5">
            <div>
                <label for="otp-digit-0" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 text-center mb-2">
                    {t('auth.enter_otp')}
                </label>
                <div class="flex justify-center gap-2 sm:gap-3" role="group" aria-label="Kode verifikasi 6 digit">
                    {#each otpDigits as digit, index}
                        <input
                            id={`otp-digit-${index}`}
                            bind:this={otpInputElements[index]}
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            pattern="[0-9]"
                            value={digit}
                            required
                            autocomplete={index === 0 ? 'one-time-code' : 'off'}
                            aria-label={`Digit ${index + 1} dari ${OTP_LENGTH}`}
                            oninput={(event) => handleOtpInput(index, event)}
                            onkeydown={(event) => handleOtpKeydown(index, event)}
                            onpaste={(event) => handleOtpPaste(index, event)}
                            class="h-12 w-10 rounded-xl border border-slate-300 bg-white text-center text-xl font-bold text-slate-900 transition-all focus:border-[#003A70] focus:outline-none focus:ring-2 focus:ring-[#003A70]/25 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-[#FFB700] dark:focus:ring-[#FFB700]/25 sm:h-14 sm:w-12 sm:text-2xl"
                        />
                    {/each}
                </div>
                {#if form.errors.code}
                    <p class="text-xs font-medium text-red-600 dark:text-red-400 text-center mt-2">
                        {form.errors.code}
                    </p>
                {/if}
            </div>

            <Button
                type="submit"
                variant="primary"
                class="w-full h-11 rounded-xl text-sm font-semibold shadow-sm hover:shadow-md transition-all"
                loading={form.processing}
                disabled={form.code.length !== 6}
            >
                {t('auth.verify_continue')}
            </Button>
        </form>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-col items-center gap-3">
            <div class="text-xs text-slate-500 dark:text-slate-400 text-center">
                {#if cooldown.state.secondsLeft > 0}
                    <span>{t('auth.resend_in')} <strong class="text-[#003A70] dark:text-[#FFD200]">{cooldown.state.secondsLeft} detik</strong></span>
                {:else}
                    <span>{t('auth.no_code')}</span>
                    <button
                        type="button"
                        onclick={handleResend}
                        disabled={isResending}
                        class="font-semibold text-[#003A70] hover:text-[#0C3B67] dark:text-[#FFD200] dark:hover:text-[#FFB700] ml-1 inline-flex items-center gap-1 cursor-pointer disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <RefreshCw class={`w-3.5 h-3.5 ${isResending ? 'animate-spin' : ''}`} />
                        {isResending ? t('auth.sending') : t('auth.resend')}
                    </button>
                {/if}
            </div>

            {#if resendError}
                <p class="text-center text-xs font-medium text-red-600 dark:text-red-400" role="alert">
                    {resendError}
                </p>
            {/if}

            <button
                type="button"
                onclick={handleLogout}
                class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer transition-colors"
            >
                {t('auth.logout_change_account')}
            </button>
        </div>
    </div>
</GuestLayout>

