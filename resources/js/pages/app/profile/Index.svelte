<script lang="ts">
    import ProfileSubnav from '@/components/profile/ProfileSubnav.svelte';
    import Badge from '@/components/ui/Badge.svelte';
    import Button from '@/components/ui/Button.svelte';
    import Card from '@/components/ui/Card.svelte';
    import Input from '@/components/ui/Input.svelte';
    import Select from '@/components/ui/Select.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { useForm } from '@inertiajs/svelte';
    import { t } from '@/lib/i18n';
    import { CheckCircle2 } from '@lucide/svelte';

    interface UserData {
        name: string;
        email: string;
        timezone: string;
        locale: string;
        default_currency: string;
        email_verified: boolean;
    }

    interface Props {
        user: UserData;
        timezones?: string[];
    }

    let { user, timezones = [] }: Props = $props();

    function getInitialFormData() {
        return {
            name: user.name,
            timezone: user.timezone || 'UTC',
            locale: user.locale || 'id',
            default_currency: user.default_currency || 'IDR',
        };
    }

    const form = useForm(getInitialFormData());

    const timezoneOptions = $derived(
        timezones.map((tz) => ({ value: tz, label: tz }))
    );

    const localeOptions = [
        { value: 'id', label: `${t('common.indonesian')} (id-ID)` },
        { value: 'en', label: `${t('common.english')} (en-US)` },
    ];

    function handleSubmit(e: SubmitEvent) {
        e.preventDefault();
        form.put('/app/profile');
    }
</script>

<AppLayout title={t('profile.title')}>
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Subnavigation Tabs -->
        <ProfileSubnav activeTab="info" />

        <Card class="p-6 sm:p-8">
            <div class="mb-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                    {t('profile.heading')}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {t('profile.description')}
                </p>
            </div>

            <form onsubmit={handleSubmit} class="space-y-4">
                <Input
                    label={t('auth.name')}
                    required
                    bind:value={form.name}
                    error={form.errors.name}
                />

                <div>
                    <Input
                        label={t('auth.email')}
                        type="email"
                        readonly
                        disabled
                        value={user.email}
                        hint={t('profile.email_permanent')}
                    />
                    <div class="mt-1 flex items-center gap-1.5">
                        {#if user.email_verified}
                            <Badge variant="success" size="sm">
                                <CheckCircle2 class="w-3 h-3" />
                                {t('common.verified')}
                            </Badge>
                        {:else}
                            <Badge variant="warning" size="sm">
                                {t('common.not_verified')}
                            </Badge>
                        {/if}
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Select
                        label={t('profile.timezone')}
                        options={timezoneOptions}
                        required
                        bind:value={form.timezone}
                        error={form.errors.timezone}
                    />

                    <Select
                        label={t('profile.locale')}
                        options={localeOptions}
                        required
                        bind:value={form.locale}
                        error={form.errors.locale}
                    />
                </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Input
                    label={t('profile.currency')}
                    value="IDR - Rupiah (Indonesia)"
                    readonly
                    disabled
                    hint={t('profile.currency_hint')}
                />
            </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <Button
                        type="submit"
                        variant="primary"
                        loading={form.processing}
                    >
                        {t('common.save')}
                    </Button>
                </div>
            </form>
        </Card>
    </div>
</AppLayout>
