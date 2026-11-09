<script lang="ts">
    import { cn } from '@/lib/utils';
    import { t } from '@/lib/i18n';
    import { Check, ChevronDown, Search, X } from '@lucide/svelte';

    export interface OptionItem {
        value: string | number;
        label: string;
        disabled?: boolean;
    }

    interface Props {
        label?: string;
        error?: string;
        hint?: string;
        id?: string;
        name?: string;
        options?: OptionItem[];
        placeholder?: string;
        class?: string;
        wrapperClass?: string;
        value?: string | number;
        disabled?: boolean;
        required?: boolean;
        searchable?: boolean;
        searchPlaceholder?: string;
        onchange?: (event: any) => void;
    }

    let {
        label,
        error,
        hint,
        id = `select-${Math.random().toString(36).substring(2, 9)}`,
        name,
        options = [],
        placeholder = `-- ${t('common.choose_option')} --`,
        class: className = '',
        wrapperClass = '',
        value = $bindable(),
        disabled = false,
        required = false,
        searchable,
        searchPlaceholder = `${t('common.search')}...`,
        onchange,
    }: Props = $props();

    let isOpen = $state(false);
    let searchQuery = $state('');
    let searchInput = $state<HTMLInputElement | null>(null);
    let triggerButton = $state<HTMLButtonElement | null>(null);
    let dropdownPosition = $state({
        top: 0,
        left: 0,
        width: 0,
        maxHeight: 240,
    });

    // Auto-enable search when options count > 5, unless explicitly overridden
    const isSearchable = $derived(
        searchable !== undefined ? searchable : options.length > 5
    );

    const selectedOption = $derived(
        options.find((opt) => String(opt.value) === String(value))
    );

    const filteredOptions = $derived(
        !searchQuery.trim()
            ? options
            : options.filter((opt) =>
                  opt.label.toLowerCase().includes(searchQuery.trim().toLowerCase())
              )
    );

    function updateDropdownPosition() {
        if (!isOpen || !triggerButton) return;

        const rect = triggerButton.getBoundingClientRect();
        const viewportPadding = 8;
        const gap = 6;
        const estimatedMenuHeight = isSearchable ? 296 : 248;
        const spaceBelow = window.innerHeight - rect.bottom - viewportPadding;
        const spaceAbove = rect.top - viewportPadding;
        const opensAbove = spaceBelow < estimatedMenuHeight && spaceAbove > spaceBelow;
        const availableSpace = Math.max(1, opensAbove ? spaceAbove - gap : spaceBelow - gap);
        const menuHeight = Math.min(estimatedMenuHeight, availableSpace);
        const listPadding = isSearchable ? 56 : 8;
        const width = Math.min(rect.width, window.innerWidth - viewportPadding * 2);
        const left = Math.min(
            Math.max(viewportPadding, rect.left),
            window.innerWidth - viewportPadding - width,
        );

        dropdownPosition = {
            top: opensAbove ? Math.max(viewportPadding, rect.top - gap - menuHeight) : rect.bottom + gap,
            left,
            width,
            maxHeight: Math.max(1, menuHeight - listPadding),
        };
    }

    $effect(() => {
        if (!isOpen) return;

        requestAnimationFrame(updateDropdownPosition);
        window.addEventListener('resize', updateDropdownPosition);
        window.addEventListener('scroll', updateDropdownPosition, true);

        return () => {
            window.removeEventListener('resize', updateDropdownPosition);
            window.removeEventListener('scroll', updateDropdownPosition, true);
        };
    });

    // Auto-focus search input when opening in searchable mode
    $effect(() => {
        if (isOpen && isSearchable) {
            requestAnimationFrame(() => {
                searchInput?.focus();
            });
        }
    });

    function toggleOpen() {
        if (disabled) return;
        isOpen = !isOpen;
        if (!isOpen) {
            searchQuery = '';
        }
    }

    function closeDropdown() {
        isOpen = false;
        searchQuery = '';
    }

    function selectOption(opt: OptionItem) {
        if (opt.disabled) return;
        value = opt.value;
        closeDropdown();
        if (onchange) {
            onchange({ target: { value: opt.value } });
        }
        triggerButton?.focus();
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
<div class={cn('w-full flex flex-col gap-1.5 relative', wrapperClass)} onkeydown={handleKeydown}>
    {#if label}
        <label for={id} class="text-xs font-semibold tracking-wide text-slate-700 dark:text-slate-300">
            {label}
            {#if required}
                <span class="text-red-500 ml-0.5">*</span>
            {/if}
        </label>
    {/if}

    <!-- Hidden native input for form submissions -->
    {#if name}
        <input type="hidden" {name} value={value ?? ''} />
    {/if}

    <div class="relative">
        <!-- Custom Trigger Button -->
        <button
            {id}
            type="button"
            bind:this={triggerButton}
            onclick={toggleOpen}
            {disabled}
            aria-haspopup="listbox"
            aria-expanded={isOpen}
            class={cn(
                'w-full h-11 px-3.5 text-sm rounded-xl border transition-all duration-150',
                'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100',
                'flex items-center justify-between text-left cursor-pointer select-none',
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
            <span
                class={cn(
                    'truncate pr-2',
                    selectedOption && selectedOption.value !== ''
                        ? 'text-slate-900 dark:text-slate-100 font-medium'
                        : 'text-slate-400 dark:text-slate-500'
                )}
            >
                {selectedOption ? selectedOption.label : placeholder}
            </span>

            <ChevronDown
                class={cn(
                    'w-4 h-4 text-slate-400 dark:text-slate-500 shrink-0 transition-transform duration-200',
                    isOpen ? 'rotate-180 text-[#003A70] dark:text-[#FFB700]' : ''
                )}
            />
        </button>

        {#if isOpen}
            <!-- Transparent backdrop to close dropdown when clicking outside -->
            <button
                type="button"
                tabindex="-1"
                class="fixed inset-0 z-40 bg-transparent cursor-default select-none border-none outline-none"
                onclick={closeDropdown}
                aria-label={t('common.close')}
            ></button>

            <!-- Floating Popover Menu -->
            <div
                class={cn(
                    'fixed z-50',
                    'rounded-xl border border-slate-200/90 dark:border-slate-800',
                    'bg-white dark:bg-slate-900 shadow-xl overflow-hidden py-1'
                )}
                style={`top: ${dropdownPosition.top}px; left: ${dropdownPosition.left}px; width: ${dropdownPosition.width}px;`}
            >
                <!-- Search Box for Combobox Mode (Options > 5) -->
                {#if isSearchable}
                    <div class="p-2 border-b border-slate-100 dark:border-slate-800/80 relative">
                        <div class="relative flex items-center">
                            <Search class="w-3.5 h-3.5 absolute left-2.5 text-slate-400 pointer-events-none" />
                            <input
                                bind:this={searchInput}
                                type="text"
                                bind:value={searchQuery}
                                placeholder={searchPlaceholder}
                                class={cn(
                                    'w-full h-8 pl-8 pr-7 text-xs rounded-lg',
                                    'bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700',
                                    'text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500',
                                    'focus:outline-none focus:ring-1 focus:ring-[#003A70] dark:focus:ring-[#FFB700] focus:border-[#003A70] dark:focus:border-[#FFB700]'
                                )}
                            />
                            {#if searchQuery}
                                <button
                                    type="button"
                                    onclick={() => {
                                        searchQuery = '';
                                        searchInput?.focus();
                                    }}
                                    class="absolute right-2 p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded cursor-pointer"
                                    aria-label={t('common.search')}
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            {/if}
                        </div>
                    </div>
                {/if}

                <!-- Options List -->
                <div
                    role="listbox"
                    class="overflow-y-auto overscroll-contain py-1 divide-y divide-transparent"
                    style={`max-height: ${dropdownPosition.maxHeight}px;`}
                >
                    {#if filteredOptions.length === 0}
                        <div class="px-3 py-6 text-center text-xs text-slate-400 dark:text-slate-500">
                            {t('common.no_matching_options', { query: searchQuery })}
                        </div>
                    {:else}
                        {#each filteredOptions as opt (opt.value)}
                            {@const isSelected = String(opt.value) === String(value)}
                            <button
                                type="button"
                                role="option"
                                aria-selected={isSelected}
                                disabled={opt.disabled}
                                onclick={() => selectOption(opt)}
                                class={cn(
                                    'w-full px-3 py-2 text-xs sm:text-sm text-left transition-colors flex items-center justify-between gap-2 cursor-pointer',
                                    isSelected
                                        ? 'bg-[#003A70]/10 dark:bg-[#003A70]/30 text-[#003A70] dark:text-[#FFD200] font-semibold'
                                        : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80',
                                    opt.disabled ? 'opacity-40 cursor-not-allowed' : ''
                                )}
                            >
                                <span class="truncate">{opt.label}</span>
                                {#if isSelected}
                                    <Check class="w-4 h-4 text-[#003A70] dark:text-[#FFD200] shrink-0" />
                                {/if}
                            </button>
                        {/each}
                    {/if}
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
