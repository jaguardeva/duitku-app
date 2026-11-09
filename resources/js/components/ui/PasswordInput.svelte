<script lang="ts">
    import { cn } from '@/lib/utils';
    import { t } from '@/lib/i18n';
    import { Eye, EyeOff } from '@lucide/svelte';
    import type { Snippet } from 'svelte';
    import type { HTMLInputAttributes } from 'svelte/elements';

    interface Props extends HTMLInputAttributes {
        label?: string;
        error?: string;
        hint?: string;
        id?: string;
        class?: string;
        leadingIcon?: Snippet;
    }

    let {
        label,
        error,
        hint,
        id = `pwd-${Math.random().toString(36).substring(2, 9)}`,
        class: className = '',
        value = $bindable(),
        leadingIcon,
        disabled,
        ...rest
    }: Props = $props();

    let showPassword = $state(false);

    function toggleVisibility() {
        showPassword = !showPassword;
    }
</script>

<div class="w-full flex flex-col gap-1.5">
    {#if label}
        <label for={id} class="text-xs font-semibold tracking-wide text-slate-700 dark:text-slate-300">
            {label}
        </label>
    {/if}

    <div class="relative flex items-center">
        {#if leadingIcon}
            <div class="absolute left-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                {@render leadingIcon()}
            </div>
        {/if}

        <input
            {id}
            type={showPassword ? 'text' : 'password'}
            bind:value
            {disabled}
            class={cn(
                'w-full h-11 px-3.5 pr-10 py-2.5 text-sm rounded-xl border transition-all duration-150',
                leadingIcon ? 'pl-10' : '',
                'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100',
                'placeholder:text-slate-400 dark:placeholder:text-slate-500',
                'focus:outline-none focus:ring-2 focus:ring-[#003A70]/20 focus:border-[#003A70] dark:focus:ring-[#FFB700]/20 dark:focus:border-[#FFB700]',
                'disabled:bg-slate-100 dark:disabled:bg-slate-800 disabled:opacity-60 disabled:cursor-not-allowed',
                error
                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
                    : 'border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-600',
                className
            )}
            {...rest}
        />

        <button
            type="button"
            onclick={toggleVisibility}
            tabindex="-1"
            class="absolute right-2.5 p-1 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 rounded cursor-pointer transition-colors focus:outline-none"
            aria-label={showPassword ? t('auth.hide_password') : t('auth.show_password')}
        >
            {#if showPassword}
                <EyeOff class="w-4 h-4" />
            {:else}
                <Eye class="w-4 h-4" />
            {/if}
        </button>
    </div>

    {#if error}
        <p class="text-xs font-medium text-red-600 dark:text-red-400 mt-0.5">{error}</p>
    {:else if hint}
        <p class="text-xs text-slate-500 dark:text-slate-400">{hint}</p>
    {/if}
</div>
