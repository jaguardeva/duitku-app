<script lang="ts">
    import { cn } from '@/lib/utils';
    import type { Snippet } from 'svelte';
    import type { HTMLInputAttributes } from 'svelte/elements';

    interface Props extends HTMLInputAttributes {
        label?: string;
        error?: string;
        hint?: string;
        id?: string;
        class?: string;
        leadingIcon?: Snippet;
        trailingIcon?: Snippet;
    }

    let {
        label,
        error,
        hint,
        id = `input-${Math.random().toString(36).substring(2, 9)}`,
        class: className = '',
        value = $bindable(),
        type = 'text',
        leadingIcon,
        trailingIcon,
        disabled,
        ...rest
    }: Props = $props();
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
            {type}
            bind:value
            {disabled}
            class={cn(
                'w-full h-11 px-3.5 py-2.5 text-sm rounded-xl border transition-all duration-150',
                'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100',
                'placeholder:text-slate-400 dark:placeholder:text-slate-500',
                'focus:outline-none focus:ring-2 focus:ring-[#003A70]/20 focus:border-[#003A70] dark:focus:ring-[#FFB700]/20 dark:focus:border-[#FFB700]',
                'disabled:bg-slate-100 dark:disabled:bg-slate-800 disabled:opacity-60 disabled:cursor-not-allowed',
                leadingIcon ? 'pl-10' : '',
                trailingIcon ? 'pr-10' : '',
                error
                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
                    : 'border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-600',
                className
            )}
            {...rest}
        />

        {#if trailingIcon}
            <div class="absolute right-3.5 flex items-center text-slate-400 dark:text-slate-500">
                {@render trailingIcon()}
            </div>
        {/if}
    </div>

    {#if error}
        <p class="text-xs font-medium text-red-600 dark:text-red-400 mt-0.5">{error}</p>
    {:else if hint}
        <p class="text-xs text-slate-500 dark:text-slate-400">{hint}</p>
    {/if}
</div>
