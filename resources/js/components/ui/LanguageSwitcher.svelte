<script lang="ts">
    import Select, { type OptionItem } from '@/components/ui/Select.svelte';
    import { update } from '@/actions/App/Http/Controllers/LocaleController';
    import { t, currentLocale } from '@/lib/i18n';
    import { router } from '@inertiajs/svelte';
    import { Globe } from '@lucide/svelte';

    interface Props {
        compact?: boolean;
        variant?: 'select' | 'toggle';
    }

    let { compact = false, variant = 'select' }: Props = $props();
    let selectedLocale = $state<string | number>(currentLocale());
    let isSubmitting = $state(false);

    $effect(() => {
        selectedLocale = currentLocale();
    });

    const localeOptions: OptionItem[] = $derived([
        { value: 'id', label: 'Bahasa Indonesia' },
        { value: 'en', label: 'English' },
    ]);

    function switchLocale(locale: 'id' | 'en') {
        if (locale === currentLocale() || isSubmitting) {
            return;
        }

        selectedLocale = locale;
        isSubmitting = true;

        router.post(update.url(), { locale }, {
            preserveScroll: true,
            preserveState: false,
            onFinish: () => (isSubmitting = false),
        });
    }

    function handleLocaleChange(event: { target: { value: string | number } }) {
        switchLocale(String(event.target.value) as 'id' | 'en');
    }

    function toggleLocale() {
        const nextLocale = currentLocale() === 'id' ? 'en' : 'id';
        switchLocale(nextLocale);
    }
</script>

{#if variant === 'toggle'}
    <button
        type="button"
        onclick={toggleLocale}
        disabled={isSubmitting}
        class="h-8 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors uppercase cursor-pointer inline-flex items-center gap-1.5 shadow-2xs select-none shrink-0"
        title={currentLocale() === 'id' ? 'Ganti ke English' : 'Switch to Bahasa Indonesia'}
        aria-label="Ganti bahasa"
    >
        <Globe class="w-3.5 h-3.5 text-[#003A70] dark:text-[#FFB700]" />
        <span>{currentLocale().toUpperCase()}</span>
    </button>
{:else}
    <div class="inline-flex items-center gap-2">
        {#if !compact}
            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                {t('common.language')}
            </span>
        {/if}
        <Select
            options={localeOptions}
            bind:value={selectedLocale}
            onchange={handleLocaleChange}
            disabled={isSubmitting}
            searchable={false}
            wrapperClass={compact ? 'w-36 sm:w-40 shrink-0' : 'w-full'}
            class={compact ? 'h-8 px-2.5 text-xs rounded-lg' : 'h-10 text-xs'}
        />
    </div>
{/if}
