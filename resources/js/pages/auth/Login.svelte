<script lang="ts">
    import Button from "@/components/ui/Button.svelte";
    import Input from "@/components/ui/Input.svelte";
    import PasswordInput from "@/components/ui/PasswordInput.svelte";
    import GuestLayout from "@/layouts/GuestLayout.svelte";
    import { t } from '@/lib/i18n';
    import { Link, useForm } from "@inertiajs/svelte";
    import { Lock, Mail } from "@lucide/svelte";

    interface Props {
        status?: string;
    }

    let { status }: Props = $props();

    const form = useForm({
        email: "",
        password: "",
        remember: false,
    });

    function handleSubmit(e: SubmitEvent) {
        e.preventDefault();
        form.post("/login", {
            onFinish: () => form.reset("password"),
        });
    }
</script>

<GuestLayout
    title={t('auth.login')}
    description="Masuk ke akun CatatDuit Anda untuk mengelola catatan pengeluaran, pemasukan, alokasi dompet, dan anggaran bulanan."
>
    <div class="space-y-4 sm:space-y-5">
        <div>
            <h1
                class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
            >
                {t('auth.welcome_back')}
            </h1>
            <p
                class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1"
            >
                {t('auth.login_description')}
            </p>
        </div>

        {#if status}
            <div
                class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold text-emerald-700 dark:text-emerald-300"
            >
                {status}
            </div>
        {/if}

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

            <div>
                <PasswordInput
                    label={t('auth.password')}
                    placeholder={t('auth.password_placeholder')}
                    required
                    autocomplete="current-password"
                    bind:value={form.password}
                    error={form.errors.password}
                >
                    {#snippet leadingIcon()}
                        <Lock class="w-4 h-4" />
                    {/snippet}
                </PasswordInput>
                <div class="flex justify-end mt-1">
                    <Link
                        href="/forgot-password"
                        class="text-xs font-semibold text-[#003A70] hover:text-[#0C3B67] dark:text-[#FFD200] dark:hover:text-[#FFB700] transition-colors"
                    >
                        {t('auth.forgot_password')}
                    </Link>
                </div>
            </div>

            <div class="flex items-center pt-0.5">
                <label
                    class="flex items-center gap-2 cursor-pointer select-none"
                >
                    <input
                        type="checkbox"
                        bind:checked={form.remember}
                        class="w-4 h-4 rounded border-slate-300 text-[#003A70] focus:ring-[#003A70]/30 dark:border-slate-700 dark:bg-slate-900"
                    />
                    <span
                        class="text-xs font-medium text-slate-600 dark:text-slate-400"
                    >
                        {t('auth.remember_me')}
                    </span>
                </label>
            </div>

            <Button
                type="submit"
                variant="primary"
                class="w-full h-11 rounded-xl text-sm font-semibold shadow-sm hover:shadow-md transition-all mt-2"
                loading={form.processing}
            >
                {t('auth.login_account')}
            </Button>
        </form>

        <div
            class="pt-4 border-t border-slate-100 dark:border-slate-800/80 text-center"
        >
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                {t('auth.no_account')}
                <Link
                    href="/register"
                    class="font-semibold text-[#003A70] hover:text-[#0C3B67] dark:text-[#FFD200] dark:hover:text-[#FFB700] ml-1 transition-colors"
                >
                    {t('auth.register_now')}
                </Link>
            </p>
        </div>
    </div>
</GuestLayout>
