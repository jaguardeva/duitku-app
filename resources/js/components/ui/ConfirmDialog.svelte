<script lang="ts">
    import Button from '@/components/ui/Button.svelte';
    import { portal } from '@/lib/portal';
    import { cn } from '@/lib/utils';
    import { t } from '@/lib/i18n';
    import { AlertTriangle, Info, LogOut, Trash2, X } from '@lucide/svelte';
    import type { Snippet } from 'svelte';

    interface Props {
        open?: boolean;
        title?: string;
        message?: string;
        confirmText?: string;
        cancelText?: string;
        variant?: 'danger' | 'warning' | 'info';
        iconType?: 'trash' | 'logout' | 'alert' | 'info';
        loading?: boolean;
        onconfirm?: () => void;
        oncancel?: () => void;
        children?: Snippet;
    }

    let {
        open = $bindable(false),
        title = t('dialogs.confirm_title'),
        message = t('dialogs.confirm_message'),
        confirmText = t('actions.confirm'),
        cancelText = t('common.cancel'),
        variant = 'danger',
        iconType,
        loading = false,
        onconfirm,
        oncancel,
        children,
    }: Props = $props();

    function handleCancel() {
        if (loading) return;
        open = false;
        oncancel?.();
    }

    function handleConfirm() {
        onconfirm?.();
    }

    function handleKeydown(e: KeyboardEvent) {
        if (e.key === 'Escape' && open && !loading) {
            handleCancel();
        }
    }

    $effect(() => {
        if (typeof document === 'undefined') return;
        if (open) {
            const originalOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            return () => {
                document.body.style.overflow = originalOverflow;
            };
        }
    });
</script>

<svelte:window onkeydown={handleKeydown} />

{#if open}
    <div
        use:portal
        class="fixed inset-0 z-[9999] isolate flex items-center justify-center p-4 overflow-y-auto bg-slate-950/80 backdrop-blur-xs animate-in fade-in duration-150"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="confirm-dialog-title"
        aria-describedby="confirm-dialog-message"
    >
        <!-- Backdrop click target -->
        <button
            type="button"
            class="fixed inset-0 z-0 w-full h-full cursor-default bg-transparent"
            onclick={handleCancel}
            aria-label={t('common.close')}
            tabindex="-1"
            disabled={loading}
        ></button>

        <!-- Dialog Box -->
        <div
            class={cn(
                'relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900',
                'border border-slate-200/90 dark:border-slate-800 shadow-2xl p-6 z-10 overflow-hidden',
                'animate-in zoom-in-95 duration-150'
            )}
        >
            <!-- Close 'X' Button on Top Right -->
            <button
                type="button"
                onclick={handleCancel}
                disabled={loading}
                class="absolute right-4 top-4 p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                aria-label={t('common.close')}
            >
                <X class="w-4 h-4" />
            </button>

            <!-- Icon Header Badge -->
            <div class="mb-4">
                {#if iconType === 'logout'}
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-200/60 dark:border-rose-900/40 shadow-xs">
                        <LogOut class="w-6 h-6 stroke-[2]" />
                    </div>
                {:else if iconType === 'trash' || (!iconType && variant === 'danger')}
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-200/60 dark:border-rose-900/40 shadow-xs">
                        <Trash2 class="w-6 h-6 stroke-[2]" />
                    </div>
                {:else if iconType === 'alert' || (!iconType && variant === 'warning')}
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200/60 dark:border-amber-900/40 shadow-xs">
                        <AlertTriangle class="w-6 h-6 stroke-[2]" />
                    </div>
                {:else}
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-200/60 dark:border-blue-900/40 shadow-xs">
                        <Info class="w-6 h-6 stroke-[2]" />
                    </div>
                {/if}
            </div>

            <!-- Content -->
            <h3 id="confirm-dialog-title" class="text-lg font-bold text-slate-900 dark:text-slate-100">
                {title}
            </h3>

            {#if message}
                <p id="confirm-dialog-message" class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                    {message}
                </p>
            {/if}

            {#if children}
                <div class="mt-3">
                    {@render children()}
                </div>
            {/if}

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <Button
                    type="button"
                    variant="outline"
                    onclick={handleCancel}
                    disabled={loading}
                    class="font-medium text-xs sm:text-sm"
                >
                    {cancelText}
                </Button>

                <Button
                    type="button"
                    variant={variant === 'danger' ? 'destructive' : variant === 'warning' ? 'accent' : 'primary'}
                    {loading}
                    disabled={loading}
                    onclick={handleConfirm}
                    class="font-semibold text-xs sm:text-sm shadow-sm"
                >
                    {confirmText}
                </Button>
            </div>
        </div>
    </div>
{/if}
