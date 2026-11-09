<script lang="ts">
    import { cn } from '@/lib/utils';
    import { t } from '@/lib/i18n';
    import { ChevronDown, Clock, X } from '@lucide/svelte';

    interface Props {
        label?: string;
        error?: string;
        hint?: string;
        id?: string;
        name?: string;
        placeholder?: string;
        class?: string;
        value?: string;
        disabled?: boolean;
        required?: boolean;
        clearable?: boolean;
        align?: 'left' | 'right';
        onchange?: (value: string) => void;
    }

    let {
        label,
        error,
        hint,
        id = `timepicker-${Math.random().toString(36).substring(2, 9)}`,
        name,
        placeholder = t('time.choose'),
        class: className = '',
        value = $bindable(''),
        disabled = false,
        required = false,
        clearable = true,
        align = 'left',
        onchange,
    }: Props = $props();

    function pad(n: number): string {
        return n < 10 ? `0${n}` : `${n}`;
    }

    function parseTime(str?: string): { hour: number; minute: number } | null {
        if (!str || typeof str !== 'string') return null;
        const parts = str.split(':');
        if (parts.length < 2) return null;
        const h = parseInt(parts[0], 10);
        const m = parseInt(parts[1], 10);
        if (isNaN(h) || isNaN(m)) return null;
        if (h < 0 || h > 23 || m < 0 || m > 59) return null;
        return { hour: h, minute: m };
    }

    let isOpen = $state(false);
    let triggerButton = $state<HTMLButtonElement | null>(null);
    let hourListRef = $state<HTMLDivElement | null>(null);
    let minuteListRef = $state<HTMLDivElement | null>(null);

    const now = new Date();
    const parsedInitial = parseTime(value);
    let selectedHour = $state(parsedInitial ? parsedInitial.hour : now.getHours());
    let selectedMinute = $state(parsedInitial ? parsedInitial.minute : now.getMinutes());

    $effect(() => {
        if (value) {
            const p = parseTime(value);
            if (p) {
                selectedHour = p.hour;
                selectedMinute = p.minute;
            }
        }
    });

    const displayLabel = $derived.by(() => {
        if (!value) return '';
        const p = parseTime(value);
        if (!p) return value;
        return `${pad(p.hour)}:${pad(p.minute)} WIB`;
    });

    function scrollToSelected() {
        requestAnimationFrame(() => {
            if (hourListRef) {
                const activeHourEl = hourListRef.querySelector('[data-active="true"]') as HTMLElement | null;
                if (activeHourEl) {
                    hourListRef.scrollTop = activeHourEl.offsetTop - hourListRef.clientHeight / 2 + activeHourEl.clientHeight / 2;
                }
            }
            if (minuteListRef) {
                const activeMinuteEl = minuteListRef.querySelector('[data-active="true"]') as HTMLElement | null;
                if (activeMinuteEl) {
                    minuteListRef.scrollTop = activeMinuteEl.offsetTop - minuteListRef.clientHeight / 2 + activeMinuteEl.clientHeight / 2;
                }
            }
        });
    }

    function toggleOpen() {
        if (disabled) return;
        isOpen = !isOpen;
        if (isOpen) {
            if (value) {
                const p = parseTime(value);
                if (p) {
                    selectedHour = p.hour;
                    selectedMinute = p.minute;
                }
            } else {
                const currentNow = new Date();
                selectedHour = currentNow.getHours();
                selectedMinute = currentNow.getMinutes();
            }
            scrollToSelected();
        }
    }

    function closeDropdown() {
        isOpen = false;
    }

    function selectHour(h: number) {
        selectedHour = h;
        const formatted = `${pad(h)}:${pad(selectedMinute)}`;
        value = formatted;
        onchange?.(formatted);
    }

    function selectMinute(m: number) {
        selectedMinute = m;
        const formatted = `${pad(selectedHour)}:${pad(m)}`;
        value = formatted;
        onchange?.(formatted);
    }

    function selectNow() {
        const currentNow = new Date();
        selectedHour = currentNow.getHours();
        selectedMinute = currentNow.getMinutes();
        const formatted = `${pad(selectedHour)}:${pad(selectedMinute)}`;
        value = formatted;
        onchange?.(formatted);
        scrollToSelected();
    }

    function selectPreset(h: number, m: number) {
        selectedHour = h;
        selectedMinute = m;
        const formatted = `${pad(h)}:${pad(m)}`;
        value = formatted;
        onchange?.(formatted);
        scrollToSelected();
    }

    function clearTime(e?: MouseEvent) {
        e?.stopPropagation();
        if (disabled) return;
        value = '';
        onchange?.('');
    }

    function handleKeydown(e: KeyboardEvent) {
        if (e.key === 'Escape' && isOpen) {
            e.preventDefault();
            e.stopPropagation();
            closeDropdown();
            triggerButton?.focus();
        }
    }
</script>

<!-- svelte-ignore a11y_no_static_element_interactions -->
<div class="w-full flex flex-col gap-1.5 relative" onkeydown={handleKeydown}>
    {#if label}
        <label for={id} class="text-xs font-semibold tracking-wide text-slate-700 dark:text-slate-300">
            {label}
            {#if required}
                <span class="text-red-500 ml-0.5">*</span>
            {/if}
        </label>
    {/if}

    <!-- Hidden native input for forms -->
    {#if name}
        <input type="hidden" {name} value={value ?? ''} />
    {/if}

    <div class="relative flex items-center">
        <!-- Trigger Button -->
        <button
            {id}
            type="button"
            bind:this={triggerButton}
            onclick={toggleOpen}
            {disabled}
            aria-haspopup="dialog"
            aria-expanded={isOpen}
            class={cn(
                'w-full h-11 pl-3.5 pr-16 text-sm rounded-xl border transition-all duration-150',
                'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100',
                'flex items-center text-left cursor-pointer select-none group',
                'focus:outline-none focus:ring-2 focus:ring-[#003A70]/20 focus:border-[#003A70] dark:focus:ring-[#FFB700]/20 dark:focus:border-[#FFB700]',
                'disabled:bg-slate-100 dark:disabled:bg-slate-800 disabled:opacity-60 disabled:cursor-not-allowed',
                isOpen
                    ? 'border-[#003A70] ring-2 ring-[#003A70]/20 dark:border-[#FFB700] dark:ring-[#FFB700]/20'
                    : '',
                error
                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
                    : 'border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-600',
                className
            )}
        >
            <div class="flex items-center gap-2.5 truncate">
                <Clock
                    class={cn(
                        'w-4 h-4 shrink-0 transition-colors',
                        isOpen || value
                            ? 'text-[#003A70] dark:text-[#FFB700]'
                            : 'text-slate-400 dark:text-slate-500'
                    )}
                />
                <span
                    class={cn(
                        'truncate',
                        value
                            ? 'text-slate-900 dark:text-slate-100 font-medium'
                            : 'text-slate-400 dark:text-slate-500'
                    )}
                >
                    {value ? displayLabel : placeholder}
                </span>
            </div>
        </button>

        <!-- Trailing Controls (Clear Button & Chevron) -->
        <div class="absolute right-3 flex items-center gap-1">
            {#if clearable && value && !disabled}
                <button
                    type="button"
                    onclick={clearTime}
                    class="p-1 rounded-md text-slate-400 hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    aria-label={t('time.clear')}
                >
                    <X class="w-3.5 h-3.5" />
                </button>
            {/if}

            <button
                type="button"
                tabindex="-1"
                onclick={toggleOpen}
                {disabled}
                class="p-0.5 text-slate-400 dark:text-slate-500 cursor-pointer disabled:cursor-not-allowed"
                aria-label={t('time.open')}
            >
                <ChevronDown
                    class={cn(
                        'w-4 h-4 transition-transform duration-200',
                        isOpen ? 'rotate-180 text-[#003A70] dark:text-[#FFB700]' : ''
                    )}
                />
            </button>
        </div>

        {#if isOpen}
            <!-- Backdrop to detect outside clicks -->
            <button
                type="button"
                tabindex="-1"
                class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs cursor-default select-none border-none outline-none animate-in fade-in duration-150"
                onclick={closeDropdown}
                aria-label={t('common.close')}
            ></button>

            <!-- TimePicker Modal -->
            <div
                class={cn(
                    'fixed left-1/2 top-1/2 z-50 w-[calc(100vw-2rem)] max-w-[20rem] -translate-x-1/2 -translate-y-1/2',
                    'max-h-[calc(100vh-2rem)] overflow-y-auto',
                    'rounded-2xl border border-slate-200/90 dark:border-slate-800',
                    'bg-white dark:bg-slate-900 shadow-2xl p-3.5 select-none',
                    'animate-in fade-in-0 zoom-in-95 duration-100'
                )}
                role="dialog"
                aria-modal="true"
                aria-label={t('time.choose')}
            >
                <!-- Digital Time Display -->
                <div class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800/80 mb-3">
                    <div class="text-2xl font-bold font-mono text-[#003A70] dark:text-[#FFB700] tracking-tight">
                        {pad(selectedHour)}
                    </div>
                    <div class="text-xl font-bold text-slate-400 dark:text-slate-500 animate-pulse">:</div>
                    <div class="text-2xl font-bold font-mono text-[#003A70] dark:text-[#FFB700] tracking-tight">
                        {pad(selectedMinute)}
                    </div>
                    <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 ml-1">WIB</span>
                </div>

                <!-- Dual Scrollable Columns: Jam & Menit -->
                <div class="grid grid-cols-2 gap-2.5 text-center">
                    <!-- Hours Column (00-23) -->
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Jam
                        </div>
                        <div
                            bind:this={hourListRef}
                            class="h-44 overflow-y-auto overscroll-contain pr-1 space-y-1 rounded-xl border border-slate-100 dark:border-slate-800/80 p-1 bg-slate-50/40 dark:bg-slate-950/20"
                        >
                            {#each Array(24) as _, h}
                                {@const isActive = selectedHour === h}
                                <button
                                    type="button"
                                    data-active={isActive}
                                    onclick={() => selectHour(h)}
                                    class={cn(
                                        'w-full h-8 rounded-lg text-xs font-semibold transition-colors flex items-center justify-center cursor-pointer',
                                        isActive
                                            ? 'bg-[#003A70] text-white dark:bg-[#FFB700] dark:text-slate-950 font-bold shadow-sm'
                                            : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800'
                                    )}
                                >
                                    {pad(h)}:00
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- Minutes Column (00-59) -->
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Menit
                        </div>
                        <div
                            bind:this={minuteListRef}
                            class="h-44 overflow-y-auto overscroll-contain pr-1 space-y-1 rounded-xl border border-slate-100 dark:border-slate-800/80 p-1 bg-slate-50/40 dark:bg-slate-950/20"
                        >
                            {#each Array(60) as _, m}
                                {@const isActive = selectedMinute === m}
                                <button
                                    type="button"
                                    data-active={isActive}
                                    onclick={() => selectMinute(m)}
                                    class={cn(
                                        'w-full h-8 rounded-lg text-xs font-semibold transition-colors flex items-center justify-center cursor-pointer',
                                        isActive
                                            ? 'bg-[#003A70] text-white dark:bg-[#FFB700] dark:text-slate-950 font-bold shadow-sm'
                                            : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800'
                                    )}
                                >
                                    :{pad(m)}
                                </button>
                            {/each}
                        </div>
                    </div>
                </div>

                <!-- Quick Minute Chips -->
                <div class="mt-2.5 flex items-center justify-between gap-1 px-0.5">
                    {#each [0, 15, 30, 45] as quickMinute}
                        <button
                            type="button"
                            onclick={() => selectMinute(quickMinute)}
                            class={cn(
                                'flex-1 py-1 text-xs font-medium rounded-lg transition-colors cursor-pointer text-center',
                                selectedMinute === quickMinute
                                    ? 'bg-[#003A70]/15 text-[#003A70] dark:bg-[#FFB700]/20 dark:text-[#FFB700] font-bold'
                                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                            )}
                        >
                            :{pad(quickMinute)}
                        </button>
                    {/each}
                </div>

                <!-- Quick Preset Shortcuts & Action Footer -->
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs gap-1.5 flex-wrap">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <button
                            type="button"
                            onclick={selectNow}
                            class="px-2.5 py-1 font-semibold rounded-lg text-[#003A70] dark:text-[#FFB700] bg-[#003A70]/10 dark:bg-[#FFB700]/10 hover:bg-[#003A70]/15 dark:hover:bg-[#FFB700]/20 transition-colors cursor-pointer"
                        >
                            Sekarang
                        </button>
                        <button
                            type="button"
                            onclick={() => selectPreset(12, 0)}
                            class="px-2 py-1 font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        >
                            12:00
                        </button>
                        <button
                            type="button"
                            onclick={() => selectPreset(19, 0)}
                            class="px-2 py-1 font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        >
                            19:00
                        </button>
                    </div>

                    <div class="flex items-center gap-1 ml-auto">
                        {#if value}
                            <button
                                type="button"
                                onclick={clearTime}
                                class="px-2 py-1 text-slate-400 hover:text-red-500 dark:hover:text-red-400 transition-colors cursor-pointer font-medium"
                            >
                                {t('actions.delete')}
                            </button>
                        {/if}
                        <button
                            type="button"
                            onclick={closeDropdown}
                            class="px-2.5 py-1 font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        {/if}
    </div>

    {#if error}
        <p class="text-xs font-medium text-red-600 dark:text-red-400 mt-0.5">{error}</p>
    {:else if hint}
        <p class="text-[11px] text-slate-500 dark:text-slate-400">{hint}</p>
    {/if}
</div>
