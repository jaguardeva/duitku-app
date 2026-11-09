<script lang="ts">
    import Button from '@/components/ui/Button.svelte';
    import Input from '@/components/ui/Input.svelte';
    import PasswordInput from '@/components/ui/PasswordInput.svelte';
    import GuestLayout from '@/layouts/GuestLayout.svelte';
    import { t } from '@/lib/i18n';
    import { Link, page, useForm } from '@inertiajs/svelte';
    import { Check, Lock, Mail, User, X } from '@lucide/svelte';

    const appName = $derived(page.props.name || 'CatatDuit');

    const detectedTimezone = typeof Intl !== 'undefined'
        ? Intl.DateTimeFormat().resolvedOptions().timeZone
        : 'UTC';

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        timezone: detectedTimezone,
    });

    // Password criteria verification
    const hasMinLength = $derived(form.password.length >= 8);
    const hasMixedCase = $derived(/[a-z]/.test(form.password) && /[A-Z]/.test(form.password));
    const hasNumber = $derived(/\d/.test(form.password));
    const hasSymbol = $derived(/[^A-Za-z0-9\s]/.test(form.password));
    const hasNoWhitespace = $derived(form.password.length > 0 && !/\s/.test(form.password));
    const isPasswordMatching = $derived(
        form.password.length > 0 && form.password === form.password_confirmation
    );

    function handleSubmit(e: SubmitEvent) {
        e.preventDefault();
        form.post('/register', {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }
</script>

<GuestLayout
    title={t('auth.register')}
    description="Daftar akun CatatDuit gratis sekarang. Mulai rapikan catatan keuangan pribadi Anda dengan mudah, aman, dan teratur."
>
    <div class="space-y-3.5 sm:space-y-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                {t('auth.register')} {appName}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                {t('auth.login_description')}
            </p>
        </div>

        <form onsubmit={handleSubmit} class="space-y-3">
            <Input
                label={t('auth.name')}
                placeholder="Contoh: Budi Santoso"
                required
                autocomplete="name"
                bind:value={form.name}
                error={form.errors.name}
            >
                {#snippet leadingIcon()}
                    <User class="w-4 h-4" />
                {/snippet}
            </Input>

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

            <PasswordInput
                label={t('auth.password')}
                placeholder={t('auth.password_combination')}
                required
                autocomplete="new-password"
                bind:value={form.password}
                error={form.errors.password}
            >
                {#snippet leadingIcon()}
                    <Lock class="w-4 h-4" />
                {/snippet}
            </PasswordInput>

            <PasswordInput
                label={t('auth.confirm_password')}
                placeholder={t('auth.repeat_password')}
                required
                autocomplete="new-password"
                bind:value={form.password_confirmation}
                error={form.errors.password_confirmation}
            >
                {#snippet leadingIcon()}
                    <Lock class="w-4 h-4" />
                {/snippet}
            </PasswordInput>

            <!-- Password Checklist Indicators -->
            {#if form.password.length > 0 || form.password_confirmation.length > 0}
                <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 text-[11px] space-y-1">
                    <p class="font-semibold text-slate-700 dark:text-slate-300">
                        {t('auth.password_rules')}
                    </p>
                    <div class="grid grid-cols-2 gap-1">
                        <span class={`flex items-center gap-1 ${hasMinLength ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-400'}`}>
                            {#if hasMinLength}<Check class="w-3 h-3 shrink-0" />{:else}<X class="w-3 h-3 shrink-0" />{/if}
                            {t('auth.min_chars')}
                        </span>
                        <span class={`flex items-center gap-1 ${hasMixedCase ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-400'}`}>
                            {#if hasMixedCase}<Check class="w-3 h-3 shrink-0" />{:else}<X class="w-3 h-3 shrink-0" />{/if}
                            {t('auth.mixed_case')}
                        </span>
                        <span class={`flex items-center gap-1 ${hasNumber ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-400'}`}>
                            {#if hasNumber}<Check class="w-3 h-3 shrink-0" />{:else}<X class="w-3 h-3 shrink-0" />{/if}
                            {t('auth.has_number')}
                        </span>
                        <span class={`flex items-center gap-1 ${hasSymbol ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-400'}`}>
                            {#if hasSymbol}<Check class="w-3 h-3 shrink-0" />{:else}<X class="w-3 h-3 shrink-0" />{/if}
                            {t('auth.has_symbol')}
                        </span>
                        <span class={`flex items-center gap-1 ${hasNoWhitespace ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-400'}`}>
                            {#if hasNoWhitespace}<Check class="w-3 h-3 shrink-0" />{:else}<X class="w-3 h-3 shrink-0" />{/if}
                            {t('auth.no_spaces')}
                        </span>
                        <span class={`flex items-center gap-1 ${isPasswordMatching ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-400'}`}>
                            {#if isPasswordMatching}<Check class="w-3 h-3 shrink-0" />{:else}<X class="w-3 h-3 shrink-0" />{/if}
                            {t('auth.matching')}
                        </span>
                    </div>
                </div>
            {/if}

            <Button
                type="submit"
                variant="primary"
                class="w-full h-11 rounded-xl text-sm font-semibold shadow-sm hover:shadow-md transition-all mt-2"
                loading={form.processing}
            >
                {t('auth.register')}
            </Button>
        </form>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 text-center">
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                {t('auth.has_account')}
                <Link
                    href="/login"
                    class="font-semibold text-[#003A70] hover:text-[#0C3B67] dark:text-[#FFD200] dark:hover:text-[#FFB700] ml-1 transition-colors"
                >
                    {t('auth.login_to_account')}
                </Link>
            </p>
        </div>
    </div>
</GuestLayout>
