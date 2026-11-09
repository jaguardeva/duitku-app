<script lang="ts">
    import { balanceVisibility } from '@/lib/balanceVisibility.svelte';
    import { t } from '@/lib/i18n';
    import { Eye, EyeOff } from '@lucide/svelte';

    interface Props {
        variant?: 'hero' | 'ghost' | 'switch';
        size?: 'sm' | 'md' | 'lg';
        class?: string;
    }

    let {
        variant = 'hero',
        size = 'md',
        class: customClass = '',
    }: Props = $props();

    const isHidden = $derived(balanceVisibility.isHidden);

    function handleClick(e: MouseEvent) {
        e.stopPropagation();
        balanceVisibility.toggle();
    }

    const heroSizeClasses = $derived(
        size === 'sm'
            ? 'w-7 h-7'
            : size === 'lg'
            ? 'w-9 h-9'
            : 'w-8 h-8 sm:w-8.5 sm:h-8.5'
    );

    const heroIconClasses = $derived(
        size === 'sm'
            ? 'w-3.5 h-3.5 stroke-[2]'
            : size === 'lg'
            ? 'w-5 h-5 stroke-[2]'
            : 'w-4 h-4 sm:w-4.5 sm:h-4.5 stroke-[2]'
    );

    const iconSizeClasses = $derived(
        size === 'sm'
            ? 'w-3.5 h-3.5 stroke-[1.8]'
            : size === 'lg'
            ? 'w-5 h-5 stroke-[1.8]'
            : 'w-4 h-4 sm:w-4.5 sm:h-4.5 stroke-[1.8]'
    );
</script>

{#if variant === 'switch'}
    <button
        type="button"
        role="switch"
        aria-checked={isHidden}
        onclick={handleClick}
        class="relative inline-flex h-7 w-14 shrink-0 items-center rounded-full p-1 transition-colors duration-200 ease-in-out cursor-pointer select-none outline-none focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0 border {isHidden ? 'bg-[#0C3B67] border-[#003A70] hover:bg-[#003A70]' : 'bg-slate-200 border-slate-300 hover:bg-slate-300'}"
        title={isHidden ? t('balance.show') : t('balance.hide')}
        aria-label={isHidden ? t('balance.show') : t('balance.hide')}
    >
        <span class="absolute left-1.5 flex items-center justify-center pointer-events-none transition-opacity duration-200 {isHidden ? 'opacity-50 text-blue-200' : 'opacity-0'}">
            <Eye class="w-3.5 h-3.5" />
        </span>
        <span class="absolute right-1.5 flex items-center justify-center pointer-events-none transition-opacity duration-200 {isHidden ? 'opacity-0' : 'opacity-40 text-slate-500'}">
            <EyeOff class="w-3.5 h-3.5" />
        </span>

        <span
            class="relative z-10 flex h-5 w-5 transform items-center justify-center rounded-full shadow-sm transition-transform duration-200 ease-in-out {isHidden ? 'translate-x-7 bg-[#FFB700] text-[#003A70]' : 'translate-x-0 bg-white text-slate-500 ring-1 ring-slate-900/5'}"
        >
            {#if isHidden}
                <EyeOff class="w-3 h-3" />
            {:else}
                <Eye class="w-3 h-3" />
            {/if}
        </span>
    </button>
{:else if variant === 'hero'}
    <button
        type="button"
        onclick={handleClick}
        class={`${heroSizeClasses} rounded-full bg-white/15 hover:bg-white/25 text-[#FFD200] hover:text-[#FFB700] border border-[#FFD200]/35 hover:border-[#FFD200]/70 shadow-xs backdrop-blur-xs transition-all duration-150 active:scale-90 hover:scale-105 cursor-pointer select-none inline-flex items-center justify-center shrink-0 ${customClass}`}
        title={isHidden ? t('balance.show') : t('balance.hide')}
        aria-label={isHidden ? t('balance.show') : t('balance.hide')}
    >
        {#if isHidden}
            <EyeOff class={`${heroIconClasses} transition-transform`} />
        {:else}
            <Eye class={`${heroIconClasses} transition-transform`} />
        {/if}
    </button>
{:else}
    <!-- Minimalist Ghost Icon Button -->
    <button
        type="button"
        onclick={handleClick}
        class={`p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 active:scale-90 transition-all duration-150 cursor-pointer select-none inline-flex items-center justify-center shrink-0 ${customClass}`}
        title={isHidden ? t('balance.show') : t('balance.hide')}
        aria-label={isHidden ? t('balance.show') : t('balance.hide')}
    >
        {#if isHidden}
            <EyeOff class={iconSizeClasses} />
        {:else}
            <Eye class={iconSizeClasses} />
        {/if}
    </button>
{/if}
