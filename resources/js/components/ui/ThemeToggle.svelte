<script lang="ts">
    import { themeManager } from '@/lib/theme.svelte';
    import { Moon, Sun } from '@lucide/svelte';

    const isDark = $derived(themeManager.theme === 'dark');

    function handleToggle() {
        themeManager.toggleTheme();
    }
</script>

<button
    type="button"
    role="switch"
    aria-checked={isDark}
    onclick={handleToggle}
    class="relative inline-flex h-7 w-14 shrink-0 items-center rounded-full p-1 transition-colors duration-200 ease-in-out cursor-pointer select-none outline-none focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0 border {isDark ? 'bg-[#0C3B67] border-[#003A70] hover:bg-[#003A70]' : 'bg-slate-200 border-slate-300 hover:bg-slate-300'}"
    title={isDark ? 'Mode Gelap (Klik untuk beralih ke Mode Terang)' : 'Mode Terang (Klik untuk beralih ke Mode Gelap)'}
    aria-label={isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'}
>
    <!-- Background track icons -->
    <span class="absolute left-1.5 flex items-center justify-center pointer-events-none transition-opacity duration-200 {isDark ? 'opacity-50 text-blue-200' : 'opacity-0'}">
        <Sun class="w-3.5 h-3.5" />
    </span>
    <span class="absolute right-1.5 flex items-center justify-center pointer-events-none transition-opacity duration-200 {isDark ? 'opacity-0' : 'opacity-40 text-slate-500'}">
        <Moon class="w-3.5 h-3.5" />
    </span>

    <!-- Sliding thumb/knob -->
    <span
        class="relative z-10 flex h-5 w-5 transform items-center justify-center rounded-full shadow-sm transition-transform duration-200 ease-in-out {isDark ? 'translate-x-7 bg-[#FFB700] text-[#003A70]' : 'translate-x-0 bg-white text-amber-500 ring-1 ring-slate-900/5'}"
    >
        {#if isDark}
            <Moon class="w-3 h-3 fill-current" />
        {:else}
            <Sun class="w-3 h-3 fill-amber-500/30" />
        {/if}
    </span>
</button>
