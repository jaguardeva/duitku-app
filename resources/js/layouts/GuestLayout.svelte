<script lang="ts">
    import LanguageSwitcher from '@/components/ui/LanguageSwitcher.svelte';
    import SeoHead from '@/components/SeoHead.svelte';
    import { t } from '@/lib/i18n';
    import ThemeToggle from "@/components/ui/ThemeToggle.svelte";
    import { page } from "@inertiajs/svelte";
    import type { Snippet } from "svelte";

    interface Props {
        title?: string;
        description?: string;
        keywords?: string;
        canonical?: string;
        noindex?: boolean;
        children?: Snippet;
    }

    let {
        title,
        description,
        keywords,
        canonical,
        noindex = false,
        children,
    }: Props = $props();

    const appName = $derived(page.props.name || 'CatatDuit');
</script>

<SeoHead {title} {description} {keywords} {canonical} {noindex} />

<div
    class="min-h-screen w-full flex flex-col justify-between relative bg-white dark:bg-[#061224] text-slate-900 dark:text-slate-100 selection:bg-[#FFB700] selection:text-[#003A70] p-4 sm:p-6 lg:p-8"
>
    <!-- Subtle Ambient Top Glow -->
    <div
        class="absolute inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-10%,rgba(0,58,112,0.06),transparent)] dark:bg-[radial-gradient(ellipse_80%_50%_at_50%_-10%,rgba(0,58,112,0.3),transparent)] pointer-events-none"
    ></div>

    <!-- Theme Toggle (Top Right Corner) -->
    <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-20 flex items-center gap-2">
        <LanguageSwitcher compact />
        <ThemeToggle />
    </div>

    <!-- Centered Form Container -->
    <div
        class="w-full max-w-[420px] mx-auto my-auto flex flex-col items-center relative z-10 py-6"
    >
        <!-- Brand Logo & Name -->
        <a
            href="/"
            class="inline-flex items-center mb-7 group select-none"
        >
            <img
                src="/favicon.png"
                alt={appName}
                class="h-28 w-28 object-contain group-hover:scale-105 transition-transform duration-200"
            />
        </a>

        <!-- Main Form Area (Cardless, Clean) -->
        <main class="w-full">
            {@render children?.()}
        </main>
    </div>

    <!-- Bottom Footer -->
    <footer
        class="w-full max-w-[420px] mx-auto pt-4 text-[11px] text-slate-400 dark:text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-1 border-t border-slate-100 dark:border-slate-800/80 shrink-0 relative z-10 text-center sm:text-left"
    >
        <p>
            &copy; {new Date().getFullYear()} {appName}. {t('common.copyright')}
        </p>
        <span class="text-[10px] text-slate-400 dark:text-slate-600"
            >{t('common.personal_finance')}</span
        >
    </footer>
</div>
