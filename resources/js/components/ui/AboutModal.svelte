<script lang="ts">
    import Button from '@/components/ui/Button.svelte';
    import Modal from '@/components/ui/Modal.svelte';
    import { t } from '@/lib/i18n';
    import { page } from '@inertiajs/svelte';
    import { CheckCircle2, Tag } from '@lucide/svelte';

    interface Props {
        open?: boolean;
        onclose?: () => void;
    }

    let {
        open = $bindable(false),
        onclose,
    }: Props = $props();

    const appName = $derived(page.props.name || 'CatatDuit');
    const appVersion = $derived((page.props.app_version as string) || '1.0.0');
    const currentYear = new Date().getFullYear();

    function handleClose() {
        open = false;
        onclose?.();
    }
</script>

<Modal bind:open size="sm" {onclose}>
    <div class="space-y-5">
        <!-- Brand Hero -->
        <div class="flex flex-col items-center text-center pt-2">
            <div class="relative mb-3">
                <div class="w-16 h-16 rounded-2xl bg-[#003A70] flex items-center justify-center p-3 shadow-lg shadow-[#003A70]/20 border-2 border-[#FFB700]/40">
                    <img
                        src="/favicon.ico"
                        alt={appName}
                        class="w-full h-full object-contain"
                    />
                </div>
                <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900 flex items-center justify-center text-white" title={t('about.status_active')}>
                    <CheckCircle2 class="w-3.5 h-3.5" />
                </div>
            </div>

            <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center justify-center gap-2">
                <span>{appName}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-[#FFB700]/20 text-[#8A6100] dark:text-[#FFD200] border border-[#FFB700]/40">
                    v{appVersion}
                </span>
            </h3>

            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs mt-2 leading-relaxed">
                {t('about.tagline')}
            </p>
        </div>

        <!-- General Info Card -->
        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 divide-y divide-slate-200/60 dark:divide-slate-800 text-xs">
            <!-- Version Row -->
            <div class="flex items-center justify-between p-3.5">
                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400 font-medium">
                    <Tag class="w-3.5 h-3.5 text-[#003A70] dark:text-[#FFB700]" />
                    <span>{t('about.version')}</span>
                </span>
                <span class="font-mono font-bold text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-900 px-2.5 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                    {appVersion}
                </span>
            </div>

            <!-- Status Row -->
            <div class="flex items-center justify-between p-3.5">
                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400 font-medium">
                    <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500" />
                    <span>{t('about.status')}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    {t('about.status_active')}
                </span>
            </div>
        </div>

        <!-- Copyright & Credits -->
        <div class="text-center text-[11px] text-slate-400 dark:text-slate-500">
            &copy; {currentYear} {appName}. {t('about.copyright')}
        </div>

        <!-- Actions -->
        <div class="pt-1">
            <Button
                variant="primary"
                class="w-full"
                onclick={handleClose}
            >
                {t('common.close')}
            </Button>
        </div>
    </div>
</Modal>
