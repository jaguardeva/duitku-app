<script lang="ts">
    import { cn } from '@/lib/utils';
    import { frontendLocale, t } from '@/lib/i18n';
    import { Calendar, ChevronDown, ChevronLeft, ChevronRight, X } from '@lucide/svelte';

    interface Props {
        label?: string;
        error?: string;
        hint?: string;
        id?: string;
        name?: string;
        placeholder?: string;
        class?: string;
        value?: string;
        min?: string;
        max?: string;
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
        id = `datepicker-${Math.random().toString(36).substring(2, 9)}`,
        name,
        placeholder = t('date.choose'),
        class: className = '',
        value = $bindable(''),
        min,
        max,
        disabled = false,
        required = false,
        clearable = true,
        align = 'left',
        onchange,
    }: Props = $props();

    const MONTH_NAMES = Array.from({ length: 12 }, (_, index) =>
        new Intl.DateTimeFormat(frontendLocale(), { month: 'long' }).format(new Date(2020, index, 1)),
    );

    const MONTH_SHORT_NAMES = Array.from({ length: 12 }, (_, index) =>
        new Intl.DateTimeFormat(frontendLocale(), { month: 'short' }).format(new Date(2020, index, 1)),
    );

    const WEEKDAYS = Array.from({ length: 7 }, (_, index) =>
        new Intl.DateTimeFormat(frontendLocale(), { weekday: 'short' }).format(new Date(2020, 5, index + 1)),
    );

    // Helper functions
    function pad(n: number): string {
        return n < 10 ? `0${n}` : `${n}`;
    }

    function toISODate(y: number, m: number, d: number): string {
        return `${y}-${pad(m + 1)}-${pad(d)}`;
    }

    function parseISO(str?: string): { year: number; month: number; day: number } | null {
        if (!str || typeof str !== 'string') return null;
        const parts = str.split('-');
        if (parts.length !== 3) return null;
        const y = parseInt(parts[0], 10);
        const m = parseInt(parts[1], 10) - 1;
        const d = parseInt(parts[2], 10);
        if (isNaN(y) || isNaN(m) || isNaN(d)) return null;
        return { year: y, month: m, day: d };
    }

    // State management
    let isOpen = $state(false);
    let viewMode = $state<'days' | 'months' | 'years'>('days');
    let triggerButton = $state<HTMLButtonElement | null>(null);

    const now = new Date();
    const parsedInitial = parseISO(value);
    let viewYear = $state(parsedInitial ? parsedInitial.year : now.getFullYear());
    let viewMonth = $state(parsedInitial ? parsedInitial.month : now.getMonth());

    // Year selector range (showing a block of 12 years)
    let yearBlockStart = $state(Math.floor((parsedInitial?.year ?? now.getFullYear()) / 12) * 12);

    // Sync view with external value changes when closed
    $effect(() => {
        if (value) {
            const p = parseISO(value);
            if (p) {
                viewYear = p.year;
                viewMonth = p.month;
                yearBlockStart = Math.floor(p.year / 12) * 12;
            }
        }
    });

    // Formatted label for trigger
    const displayLabel = $derived.by(() => {
        if (!value) return '';
        const p = parseISO(value);
        if (!p) return value;
        return `${p.day} ${MONTH_SHORT_NAMES[p.month]} ${p.year}`;
    });

    const isToday = (y: number, m: number, d: number) => {
        return (
            y === now.getFullYear() &&
            m === now.getMonth() &&
            d === now.getDate()
        );
    };

    const isSelected = (y: number, m: number, d: number) => {
        if (!value) return false;
        const p = parseISO(value);
        return !!p && p.year === y && p.month === m && p.day === d;
    };

    const isDateDisabled = (y: number, m: number, d: number) => {
        const iso = toISODate(y, m, d);
        if (min && iso < min) return true;
        if (max && iso > max) return true;
        return false;
    };

    // Calendar grid cells (always 42 items for stable 6 rows)
    const calendarCells = $derived.by(() => {
        const firstDayOfMonth = new Date(viewYear, viewMonth, 1);
        // Monday is index 0: (firstDay.getDay() + 6) % 7
        const startDayOffset = (firstDayOfMonth.getDay() + 6) % 7;
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        const daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();

        const cells: Array<{
            year: number;
            month: number;
            day: number;
            isCurrentMonth: boolean;
            isToday: boolean;
            isSelected: boolean;
            isDisabled: boolean;
            isSunday: boolean;
        }> = [];

        // Previous month days
        for (let i = startDayOffset - 1; i >= 0; i--) {
            const d = daysInPrevMonth - i;
            const m = viewMonth === 0 ? 11 : viewMonth - 1;
            const y = viewMonth === 0 ? viewYear - 1 : viewYear;
            const dayOfWeek = (new Date(y, m, d).getDay() + 6) % 7;
            cells.push({
                year: y,
                month: m,
                day: d,
                isCurrentMonth: false,
                isToday: isToday(y, m, d),
                isSelected: isSelected(y, m, d),
                isDisabled: isDateDisabled(y, m, d),
                isSunday: dayOfWeek === 6,
            });
        }

        // Current month days
        for (let d = 1; d <= daysInMonth; d++) {
            const dayOfWeek = (new Date(viewYear, viewMonth, d).getDay() + 6) % 7;
            cells.push({
                year: viewYear,
                month: viewMonth,
                day: d,
                isCurrentMonth: true,
                isToday: isToday(viewYear, viewMonth, d),
                isSelected: isSelected(viewYear, viewMonth, d),
                isDisabled: isDateDisabled(viewYear, viewMonth, d),
                isSunday: dayOfWeek === 6,
            });
        }

        // Next month days to reach 42 cells
        const remaining = 42 - cells.length;
        for (let d = 1; d <= remaining; d++) {
            const m = viewMonth === 11 ? 0 : viewMonth + 1;
            const y = viewMonth === 11 ? viewYear + 1 : viewYear;
            const dayOfWeek = (new Date(y, m, d).getDay() + 6) % 7;
            cells.push({
                year: y,
                month: m,
                day: d,
                isCurrentMonth: false,
                isToday: isToday(y, m, d),
                isSelected: isSelected(y, m, d),
                isDisabled: isDateDisabled(y, m, d),
                isSunday: dayOfWeek === 6,
            });
        }

        return cells;
    });

    function toggleOpen() {
        if (disabled) return;
        isOpen = !isOpen;
        if (isOpen) {
            viewMode = 'days';
            if (value) {
                const p = parseISO(value);
                if (p) {
                    viewYear = p.year;
                    viewMonth = p.month;
                    yearBlockStart = Math.floor(p.year / 12) * 12;
                }
            }
        }
    }

    function closeCalendar() {
        isOpen = false;
        viewMode = 'days';
    }

    function selectDate(isoDate: string) {
        value = isoDate;
        closeCalendar();
        onchange?.(isoDate);
        triggerButton?.focus();
    }

    function handleDayClick(cell: { year: number; month: number; day: number; isDisabled: boolean }) {
        if (cell.isDisabled) return;
        const iso = toISODate(cell.year, cell.month, cell.day);
        selectDate(iso);
    }

    function selectToday() {
        const iso = toISODate(now.getFullYear(), now.getMonth(), now.getDate());
        if (isDateDisabled(now.getFullYear(), now.getMonth(), now.getDate())) return;
        selectDate(iso);
    }

    function selectYesterday() {
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);
        const y = yesterday.getFullYear();
        const m = yesterday.getMonth();
        const d = yesterday.getDate();
        if (isDateDisabled(y, m, d)) return;
        selectDate(toISODate(y, m, d));
    }

    function clearDate(e?: MouseEvent) {
        e?.stopPropagation();
        if (disabled) return;
        value = '';
        onchange?.('');
    }

    function prevMonth() {
        if (viewMonth === 0) {
            viewMonth = 11;
            viewYear -= 1;
        } else {
            viewMonth -= 1;
        }
    }

    function nextMonth() {
        if (viewMonth === 11) {
            viewMonth = 0;
            viewYear += 1;
        } else {
            viewMonth += 1;
        }
    }

    function prevYearBlock() {
        yearBlockStart -= 12;
    }

    function nextYearBlock() {
        yearBlockStart += 12;
    }

    function selectMonth(mIndex: number) {
        viewMonth = mIndex;
        viewMode = 'days';
    }

    function selectYear(y: number) {
        viewYear = y;
        viewMode = 'months';
    }

    function handleKeydown(e: KeyboardEvent) {
        if (e.key === 'Escape' && isOpen) {
            e.preventDefault();
            e.stopPropagation();
            closeCalendar();
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
                <Calendar
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
                    onclick={clearDate}
                    class="p-1 rounded-md text-slate-400 hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    aria-label={t('date.clear')}
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
                aria-label={t('date.open')}
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
                onclick={closeCalendar}
                aria-label={t('date.close')}
            ></button>

            <!-- Calendar Modal -->
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
                aria-label={t('date.choose')}
            >
                <!-- Header / Mode Switcher -->
                <div class="flex items-center justify-between mb-3 px-1">
                    {#if viewMode === 'days'}
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                onclick={() => (viewMode = 'months')}
                                class="text-sm font-bold text-slate-900 dark:text-slate-100 hover:text-[#003A70] dark:hover:text-[#FFB700] hover:bg-slate-100 dark:hover:bg-slate-800 px-2 py-1 rounded-lg transition-colors cursor-pointer"
                            >
                                {MONTH_NAMES[viewMonth]}
                            </button>
                            <button
                                type="button"
                                onclick={() => {
                                    yearBlockStart = Math.floor(viewYear / 12) * 12;
                                    viewMode = 'years';
                                }}
                                class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-[#003A70] dark:hover:text-[#FFB700] hover:bg-slate-100 dark:hover:bg-slate-800 px-2 py-1 rounded-lg transition-colors cursor-pointer"
                            >
                                {viewYear}
                            </button>
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                onclick={prevMonth}
                                class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                aria-label={t('date.previous_month')}
                            >
                                <ChevronLeft class="w-4 h-4" />
                            </button>
                            <button
                                type="button"
                                onclick={nextMonth}
                                class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                aria-label={t('date.next_month')}
                            >
                                <ChevronRight class="w-4 h-4" />
                            </button>
                        </div>
                    {:else if viewMode === 'months'}
                        <button
                            type="button"
                            onclick={() => (viewMode = 'days')}
                            class="text-sm font-bold text-slate-900 dark:text-slate-100 hover:text-[#003A70] dark:hover:text-[#FFB700] px-2 py-1 rounded-lg transition-colors cursor-pointer flex items-center gap-1"
                        >
                            <ChevronLeft class="w-4 h-4" /> {t('date.choose_month')} ({viewYear})
                        </button>
                    {:else if viewMode === 'years'}
                        <div class="flex items-center justify-between w-full">
                            <button
                                type="button"
                                onclick={() => (viewMode = 'days')}
                                class="text-sm font-bold text-slate-900 dark:text-slate-100 hover:text-[#003A70] dark:hover:text-[#FFB700] px-2 py-1 rounded-lg transition-colors cursor-pointer flex items-center gap-1"
                            >
                                <ChevronLeft class="w-4 h-4" /> {yearBlockStart} - {yearBlockStart + 11}
                            </button>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    onclick={prevYearBlock}
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                    aria-label={t('date.previous_decade')}
                                >
                                    <ChevronLeft class="w-4 h-4" />
                                </button>
                                <button
                                    type="button"
                                    onclick={nextYearBlock}
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                    aria-label={t('date.next_decade')}
                                >
                                    <ChevronRight class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    {/if}
                </div>

                <!-- Body Views -->
                {#if viewMode === 'days'}
                    <!-- Weekday labels -->
                    <div class="grid grid-cols-7 mb-1.5 text-center">
                        {#each WEEKDAYS as day, idx}
                            <div
                                class={cn(
                                    'text-[11px] font-semibold tracking-wider uppercase py-1',
                                    idx === 6
                                        ? 'text-rose-500/80 dark:text-rose-400'
                                        : 'text-slate-400 dark:text-slate-500'
                                )}
                            >
                                {day}
                            </div>
                        {/each}
                    </div>

                    <!-- Day Cells Grid (42 cells) -->
                    <div class="grid grid-cols-7 gap-1 text-center">
                        {#each calendarCells as cell}
                            <button
                                type="button"
                                disabled={cell.isDisabled}
                                onclick={() => handleDayClick(cell)}
                                class={cn(
                                    'h-8 sm:h-9 w-full rounded-xl text-xs sm:text-sm font-medium transition-all duration-100 flex items-center justify-center relative cursor-pointer',
                                    // Selected state
                                    cell.isSelected
                                        ? 'bg-[#003A70] text-white dark:bg-[#FFB700] dark:text-slate-950 font-bold shadow-sm shadow-[#003A70]/30'
                                        : [
                                              // Not selected hover
                                              'hover:bg-slate-100 dark:hover:bg-slate-800',
                                              // Month distinctions
                                              cell.isCurrentMonth
                                                  ? cell.isSunday
                                                      ? 'text-rose-600 dark:text-rose-400 font-semibold'
                                                      : 'text-slate-800 dark:text-slate-100'
                                                  : 'text-slate-300 dark:text-slate-600',
                                          ],
                                    // Today indicator border (when not selected)
                                    cell.isToday && !cell.isSelected
                                        ? 'ring-1.5 ring-inset ring-[#003A70] dark:ring-[#FFB700] font-bold text-[#003A70] dark:text-[#FFB700]'
                                        : '',
                                    // Disabled
                                    cell.isDisabled
                                        ? 'opacity-25 cursor-not-allowed hover:bg-transparent dark:hover:bg-transparent pointer-events-none'
                                        : ''
                                )}
                            >
                                {cell.day}
                            </button>
                        {/each}
                    </div>

                    <!-- Quick Shortcuts Footer -->
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                onclick={selectToday}
                                class="px-2.5 py-1 font-semibold rounded-lg text-[#003A70] dark:text-[#FFB700] bg-[#003A70]/10 dark:bg-[#FFB700]/10 hover:bg-[#003A70]/15 dark:hover:bg-[#FFB700]/20 transition-colors cursor-pointer"
                            >
                                {t('date.today')}
                            </button>
                            <button
                                type="button"
                                onclick={selectYesterday}
                                class="px-2.5 py-1 font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                            >
                                {t('date.yesterday')}
                            </button>
                        </div>

                        {#if value}
                            <button
                                type="button"
                                onclick={clearDate}
                                class="px-2 py-1 text-slate-400 hover:text-red-500 dark:hover:text-red-400 transition-colors cursor-pointer font-medium"
                            >
                                {t('actions.delete')}
                            </button>
                        {/if}
                    </div>
                {:else if viewMode === 'months'}
                    <!-- Month Selector Grid -->
                    <div class="grid grid-cols-3 gap-2 py-1">
                        {#each MONTH_NAMES as mName, mIdx}
                            {@const isCurrentMonthActive = viewMonth === mIdx}
                            <button
                                type="button"
                                onclick={() => selectMonth(mIdx)}
                                class={cn(
                                    'py-2.5 px-1 rounded-xl text-xs font-semibold transition-all cursor-pointer',
                                    isCurrentMonthActive
                                        ? 'bg-[#003A70] text-white dark:bg-[#FFB700] dark:text-slate-950 shadow-sm'
                                        : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800'
                                )}
                            >
                                {mName}
                            </button>
                        {/each}
                    </div>
                {:else if viewMode === 'years'}
                    <!-- Year Selector Grid (12 years) -->
                    <div class="grid grid-cols-3 gap-2 py-1">
                        {#each Array(12) as _, idx}
                            {@const y = yearBlockStart + idx}
                            {@const isCurrentYearActive = viewYear === y}
                            <button
                                type="button"
                                onclick={() => selectYear(y)}
                                class={cn(
                                    'py-2.5 px-1 rounded-xl text-xs font-semibold transition-all cursor-pointer',
                                    isCurrentYearActive
                                        ? 'bg-[#003A70] text-white dark:bg-[#FFB700] dark:text-slate-950 shadow-sm'
                                        : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800'
                                )}
                            >
                                {y}
                            </button>
                        {/each}
                    </div>
                {/if}
            </div>
        {/if}
    </div>

    {#if error}
        <p class="text-xs font-medium text-red-600 dark:text-red-400 mt-0.5">{error}</p>
    {:else if hint}
        <p class="text-[11px] text-slate-500 dark:text-slate-400">{hint}</p>
    {/if}
</div>
