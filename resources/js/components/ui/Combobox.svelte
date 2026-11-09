<script lang="ts">
    import Select, { type OptionItem } from './Select.svelte';
    import { t } from '@/lib/i18n';

    export type { OptionItem };

    interface Props {
        label?: string;
        error?: string;
        hint?: string;
        id?: string;
        name?: string;
        options?: OptionItem[];
        placeholder?: string;
        class?: string;
        value?: string | number;
        disabled?: boolean;
        required?: boolean;
        searchPlaceholder?: string;
        onchange?: (event: any) => void;
    }

    let {
        searchPlaceholder = `${t('common.search')}...`,
        value = $bindable(),
        ...rest
    }: Props = $props();
</script>

<Select
    {...rest}
    searchable={true}
    {searchPlaceholder}
    bind:value
/>
