<script lang="ts">
    import { cn } from "@/lib/utils";
    import { FolderOpen } from "@lucide/svelte";
    import type { Snippet } from "svelte";

    interface Props {
        title: string;
        description?: string;
        icon?: Snippet;
        action?: Snippet;
        class?: string;
    }

    let {
        title,
        description,
        icon,
        action,
        class: className = "",
    }: Props = $props();
</script>

<div
    class={cn(
        "flex flex-col items-center justify-center p-8 sm:p-12 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30",
        className,
    )}
>
    <div
        class="w-12 h-12 rounded-xl flex items-center justify-center bg-[#003A70]/10 dark:bg-[#003A70]/30 text-[#003A70] dark:text-[#FFB700] mb-4 shadow-xs"
    >
        {#if icon}
            {@render icon()}
        {:else}
            <FolderOpen class="w-6 h-6" />
        {/if}
    </div>

    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-1">
        {title}
    </h3>

    {#if description}
        <p
            class="text-xs md:text-sm text-slate-500 dark:text-slate-400 max-w-sm mb-6"
        >
            {description}
        </p>
    {/if}

    {#if action}
        <div>
            {@render action()}
        </div>
    {/if}
</div>
