<script lang="ts">
    import { portal } from '@/lib/portal';
    import { cn } from '@/lib/utils';
    import { X } from '@lucide/svelte';
    import type { Snippet } from 'svelte';

    interface Props {
        open?: boolean;
        title?: string;
        size?: 'sm' | 'md' | 'lg' | 'xl';
        onclose?: () => void;
        children?: Snippet;
        footer?: Snippet;
    }

    let {
        open = $bindable(false),
        title,
        size = 'md',
        onclose,
        children,
        footer,
    }: Props = $props();

    const sizeStyles = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
    };

    function handleClose() {
        open = false;
        onclose?.();
    }

    function handleKeydown(event: KeyboardEvent) {
        if (event.key === 'Escape' && open) {
            handleClose();
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
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop click target -->
        <button
            type="button"
            class="fixed inset-0 z-0 w-full h-full cursor-default bg-transparent"
            onclick={handleClose}
            aria-label="Tutup modal"
            tabindex="-1"
        ></button>

        <div
            class={cn(
                'relative z-10 w-full rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xl overflow-hidden',
                'animate-in zoom-in-95 duration-150',
                sizeStyles[size]
            )}
        >
            {#if title}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800/80">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                        {title}
                    </h3>
                    <button
                        type="button"
                        onclick={handleClose}
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        aria-label="Tutup"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>
            {/if}

            <div class="p-6">
                {@render children?.()}
            </div>

            {#if footer}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50">
                    {@render footer()}
                </div>
            {/if}
        </div>
    </div>
{/if}
