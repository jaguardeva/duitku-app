import { page } from '@inertiajs/svelte';

type TranslationValue = string | Record<string, unknown>;

function getValue(key: string): TranslationValue | undefined {
    const translations = page.props.translations as
        | Record<string, unknown>
        | undefined;

    return key.split('.').reduce<unknown>((value, part) => {
        if (!value || typeof value !== 'object') {
            return undefined;
        }

        return (value as Record<string, unknown>)[part];
    }, translations) as TranslationValue | undefined;
}

export function t(
    key: string,
    replacements: Record<string, string | number> = {},
): string {
    const value = getValue(key);
    const text = typeof value === 'string' ? value : key;

    return Object.entries(replacements).reduce(
        (result, [placeholder, replacement]) =>
            result.replaceAll(`:${placeholder}`, String(replacement)),
        text,
    );
}

export function currentLocale(): 'id' | 'en' {
    return page.props.locale === 'en' ? 'en' : 'id';
}

export function frontendLocale(): 'id-ID' | 'en-US' {
    return currentLocale() === 'en' ? 'en-US' : 'id-ID';
}
