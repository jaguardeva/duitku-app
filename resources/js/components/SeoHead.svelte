<script lang="ts">
    import { page } from '@inertiajs/svelte';

    interface Props {
        title?: string;
        description?: string;
        keywords?: string;
        canonical?: string;
        noindex?: boolean;
        ogTitle?: string;
        ogDescription?: string;
        ogImage?: string;
        ogType?: 'website' | 'article';
        jsonLd?: Record<string, any>;
    }

    let {
        title,
        description,
        keywords,
        canonical,
        noindex = false,
        ogTitle,
        ogDescription,
        ogImage = '/og-image.png',
        ogType = 'website',
        jsonLd,
    }: Props = $props();

    const appName = $derived(page.props.name || 'CatatDuit');
    const fullTitle = $derived(title ? `${title} - ${appName}` : `${appName} - Aplikasi Pencatatan Keuangan & Manajemen Anggaran`);
    const activeDescription = $derived(
        description ||
            'CatatDuit adalah aplikasi pencatatan keuangan pribadi modern untuk mengelola pengeluaran, memantau pemasukan, alokasi dompet multi-rekening, dan perencanaan anggaran bulanan.'
    );
    const activeOgTitle = $derived(ogTitle || fullTitle);
    const activeOgDescription = $derived(ogDescription || activeDescription);
    const jsonLdMarkup = $derived(
        jsonLd
            ? '<script type="application/ld+json">' + JSON.stringify(jsonLd) + '<' + '/script>'
            : '',
    );
</script>

<svelte:head>
    <title>{fullTitle}</title>

    {#if noindex}
        <meta name="robots" content="noindex, nofollow" />
    {:else}
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    {/if}

    <meta name="description" content={activeDescription} />

    {#if keywords}
        <meta name="keywords" content={keywords} />
    {/if}

    {#if canonical}
        <link rel="canonical" href={canonical} />
    {/if}

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn) -->
    <meta property="og:title" content={activeOgTitle} />
    <meta property="og:description" content={activeOgDescription} />
    <meta property="og:type" content={ogType} />
    {#if ogImage}
        <meta property="og:image" content={ogImage} />
    {/if}

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content={activeOgTitle} />
    <meta name="twitter:description" content={activeOgDescription} />
    {#if ogImage}
        <meta name="twitter:image" content={ogImage} />
    {/if}

    {#if jsonLd}
        {@html jsonLdMarkup}
    {/if}
</svelte:head>
