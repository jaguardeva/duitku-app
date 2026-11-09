<script lang="ts">
    import { cn } from '@/lib/utils';
    import type { HTMLInputAttributes } from 'svelte/elements';

    interface Props extends Omit<HTMLInputAttributes, 'value' | 'size'> {
        label?: string;
        error?: string;
        hint?: string;
        id?: string;
        class?: string;
        value?: number | string;
        placeholder?: string;
        size?: 'md' | 'lg';
        prefix?: string;
    }

    let {
        label,
        error,
        hint,
        id = `currency-input-${Math.random().toString(36).substring(2, 9)}`,
        name,
        class: className = '',
        value = $bindable(0),
        placeholder = '0',
        size = 'md',
        prefix = 'Rp',
        required = false,
        disabled = false,
        readonly = false,
        ...rest
    }: Props = $props();

    function formatToRupiahDigits(val: number | string | null | undefined): string {
        if (val === null || val === undefined || val === '') return '';
        const numStr = String(val).replace(/\D/g, '');
        if (!numStr) return '';
        return numStr.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    let inputElement: HTMLInputElement | null = $state(null);
    let displayValue = $state(formatToRupiahDigits(value));

    // Synchronize displayValue when value changes externally (e.g. form reset or prefill)
    $effect(() => {
        const currentClean = displayValue.replace(/\D/g, '');
        const newClean = String(value ?? '').replace(/\D/g, '');
        if (currentClean !== newClean) {
            displayValue = formatToRupiahDigits(value);
        }
    });

    function handleInput(e: Event) {
        const el = e.currentTarget as HTMLInputElement;
        const rawText = el.value;
        const cursorPos = el.selectionStart ?? 0;

        // Calculate count of pure digits before current cursor position
        const digitsBeforeCursor = rawText.slice(0, cursorPos).replace(/\D/g, '').length;

        // Extract pure digits
        const cleanDigits = rawText.replace(/\D/g, '');

        if (cleanDigits === '') {
            value = 0;
            displayValue = '';
        } else {
            const num = parseInt(cleanDigits, 10);
            value = num;
            displayValue = cleanDigits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Restore cursor position based on digit count
        let newCursorPos = 0;
        let countedDigits = 0;
        for (let i = 0; i < displayValue.length; i++) {
            if (/\d/.test(displayValue[i])) {
                countedDigits++;
            }
            if (countedDigits === digitsBeforeCursor) {
                newCursorPos = i + 1;
                break;
            }
        }
        if (digitsBeforeCursor === 0) {
            newCursorPos = 0;
        }

        requestAnimationFrame(() => {
            if (inputElement) {
                inputElement.setSelectionRange(newCursorPos, newCursorPos);
            }
        });
    }
</script>

<div class="w-full flex flex-col gap-1.5">
    {#if label}
        <label for={id} class="text-xs font-semibold tracking-wide text-slate-700 dark:text-slate-300">
            {label}
            {#if required}
                <span class="text-red-500 ml-0.5">*</span>
            {/if}
        </label>
    {/if}

    <div class="relative flex items-center">
        <!-- Prefix Label / Badge -->
        <div
            class={cn(
                'absolute left-3.5 flex items-center pointer-events-none select-none z-10',
                size === 'lg' ? 'top-1/2 -translate-y-1/2' : ''
            )}
        >
            <span
                class={cn(
                    'font-mono font-bold uppercase transition-colors',
                    size === 'lg'
                        ? 'text-base sm:text-lg text-slate-400 dark:text-slate-500'
                        : 'text-xs px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/80'
                )}
            >
                {prefix}
            </span>
        </div>

        <input
            {id}
            {name}
            type="text"
            inputmode="numeric"
            autocomplete="off"
            bind:this={inputElement}
            value={displayValue}
            oninput={handleInput}
            {placeholder}
            {required}
            {disabled}
            {readonly}
            class={cn(
                'w-full rounded-xl border transition-all duration-150 font-mono font-bold tracking-tight',
                'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100',
                'placeholder:text-slate-300 dark:placeholder:text-slate-600',
                'focus:outline-none focus:ring-2 focus:ring-[#003A70]/20 focus:border-[#003A70] dark:focus:ring-[#FFB700]/20 dark:focus:border-[#FFB700]',
                'disabled:bg-slate-100 dark:disabled:bg-slate-800 disabled:opacity-60 disabled:cursor-not-allowed',
                size === 'lg'
                    ? 'h-14 pl-14 sm:pl-16 pr-4 text-2xl sm:text-3xl'
                    : 'h-11 pl-13 pr-3.5 text-sm sm:text-base',
                error
                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
                    : 'border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-600',
                className
            )}
            {...rest}
        />
    </div>

    {#if error}
        <p class="text-xs font-medium text-red-600 dark:text-red-400 mt-0.5">
            {error}
        </p>
    {:else if hint}
        <p class="text-[11px] text-slate-500 dark:text-slate-400">
            {hint}
        </p>
    {/if}
</div>
