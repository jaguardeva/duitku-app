<script lang="ts">
    import ProfileSubnav from "@/components/profile/ProfileSubnav.svelte";
    import SessionManagement from "@/components/profile/SessionManagement.svelte";
    import Badge from "@/components/ui/Badge.svelte";
    import Button from "@/components/ui/Button.svelte";
    import Card from "@/components/ui/Card.svelte";
    import PasswordInput from "@/components/ui/PasswordInput.svelte";
    import AppLayout from "@/layouts/AppLayout.svelte";
    import { t } from '@/lib/i18n';
    import { useForm } from "@inertiajs/svelte";
    import type { SessionItem } from "@/types";
    import { Check, CheckCircle2, X } from "@lucide/svelte";

    interface Props {
        email: string;
        email_verified: boolean;
        sessions: SessionItem[];
    }

    let { email, email_verified, sessions = [] }: Props = $props();

    const form = useForm({
        current_password: "",
        password: "",
        password_confirmation: "",
    });

    const hasMinLength = $derived(form.password.length >= 8);
    const hasMixedCase = $derived(
        /[a-z]/.test(form.password) && /[A-Z]/.test(form.password),
    );
    const hasNumber = $derived(/\d/.test(form.password));
    const hasSymbol = $derived(/[^A-Za-z0-9\s]/.test(form.password));
    const hasNoWhitespace = $derived(
        form.password.length > 0 && !/\s/.test(form.password),
    );
    const isMatching = $derived(
        form.password.length > 0 &&
            form.password === form.password_confirmation,
    );

    function handleSubmit(e: SubmitEvent) {
        e.preventDefault();
        form.put("/app/profile/security/password", {
            onSuccess: () => {
                form.reset();
            },
        });
    }
</script>

<AppLayout title={t('navigation.security')}>
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Subnavigation Tabs -->
        <ProfileSubnav activeTab="security" />

        <!-- Verification Status Card -->
        <Card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h4
                        class="text-sm font-bold text-slate-900 dark:text-slate-100"
                    >
                        {t('profile.verification_status')}
                    </h4>
                    <p
                        class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"
                    >
                        {email}
                    </p>
                </div>
                {#if email_verified}
                    <Badge
                        variant="success"
                        size="md"
                        class="flex items-center gap-1.5"
                    >
                        <CheckCircle2 class="w-4 h-4" />
                        {t('common.verified')}
                    </Badge>
                {:else}
                    <Badge variant="warning" size="md">
                        {t('common.not_verified')}
                    </Badge>
                {/if}
            </div>
        </Card>

        <!-- Change Password Card -->
        <Card class="p-6 sm:p-8">
            <div class="mb-6">
                <h3
                    class="text-base font-bold text-slate-900 dark:text-slate-100"
                >
                    {t('profile.update_password')}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {t('profile.password_description')}
                </p>
            </div>

            <form onsubmit={handleSubmit} class="space-y-4">
                <PasswordInput
                    label={t('profile.current_password')}
                    placeholder={t('sessions.password_current')}
                    required
                    autocomplete="current-password"
                    bind:value={form.current_password}
                    error={form.errors.current_password}
                />

                <PasswordInput
                    label={t('profile.new_password')}
                    placeholder={t('profile.new_password_placeholder')}
                    required
                    autocomplete="new-password"
                    bind:value={form.password}
                    error={form.errors.password}
                />

                <PasswordInput
                    label={t('profile.confirm_new_password')}
                    placeholder={t('auth.repeat_password')}
                    required
                    autocomplete="new-password"
                    bind:value={form.password_confirmation}
                    error={form.errors.password_confirmation}
                />

                <!-- Criteria Checklist -->
                {#if form.password.length > 0 || form.password_confirmation.length > 0}
                    <div
                        class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-xs space-y-1.5"
                    >
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                            <span
                                class={`flex items-center gap-1.5 ${hasMinLength ? "text-emerald-600 dark:text-emerald-400 font-medium" : "text-slate-400"}`}
                            >
                                {#if hasMinLength}<Check
                                        class="w-3.5 h-3.5"
                                    />{:else}<X class="w-3.5 h-3.5" />{/if}
                                {t('auth.min_chars')}
                            </span>
                            <span
                                class={`flex items-center gap-1.5 ${hasMixedCase ? "text-emerald-600 dark:text-emerald-400 font-medium" : "text-slate-400"}`}
                            >
                                {#if hasMixedCase}<Check
                                        class="w-3.5 h-3.5"
                                    />{:else}<X class="w-3.5 h-3.5" />{/if}
                                {t('auth.mixed_case')}
                            </span>
                            <span
                                class={`flex items-center gap-1.5 ${hasNumber ? "text-emerald-600 dark:text-emerald-400 font-medium" : "text-slate-400"}`}
                            >
                                {#if hasNumber}<Check
                                        class="w-3.5 h-3.5"
                                    />{:else}<X class="w-3.5 h-3.5" />{/if}
                                {t('auth.has_number')}
                            </span>
                            <span
                                class={`flex items-center gap-1.5 ${hasSymbol ? "text-emerald-600 dark:text-emerald-400 font-medium" : "text-slate-400"}`}
                            >
                                {#if hasSymbol}<Check
                                        class="w-3.5 h-3.5"
                                    />{:else}<X class="w-3.5 h-3.5" />{/if}
                                {t('auth.has_symbol')}
                            </span>
                            <span
                                class={`flex items-center gap-1.5 ${hasNoWhitespace ? "text-emerald-600 dark:text-emerald-400 font-medium" : "text-slate-400"}`}
                            >
                                {#if hasNoWhitespace}<Check
                                        class="w-3.5 h-3.5"
                                    />{:else}<X class="w-3.5 h-3.5" />{/if}
                                {t('auth.no_spaces')}
                            </span>
                            <span
                                class={`flex items-center gap-1.5 ${isMatching ? "text-emerald-600 dark:text-emerald-400 font-medium" : "text-slate-400"}`}
                            >
                                {#if isMatching}<Check
                                        class="w-3.5 h-3.5"
                                    />{:else}<X class="w-3.5 h-3.5" />{/if}
                                {t('auth.matching')}
                            </span>
                        </div>
                    </div>
                {/if}

                <div
                    class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end"
                >
                    <Button
                        type="submit"
                        variant="primary"
                        loading={form.processing}
                    >
                        {t('profile.save_password')}
                    </Button>
                </div>
            </form>
        </Card>

        <SessionManagement {sessions} />
    </div>
</AppLayout>
