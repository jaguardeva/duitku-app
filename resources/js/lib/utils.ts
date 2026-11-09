import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { t, frontendLocale } from './i18n';

export function cn(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs));
}

export function formatCurrency(
    amount: number | string,
    currencyCode = 'IDR',
    locale = frontendLocale(),
): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    if (isNaN(num)) return '0';

    try {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currencyCode,
            minimumFractionDigits: currencyCode === 'IDR' ? 0 : 2,
            maximumFractionDigits: currencyCode === 'IDR' ? 0 : 2,
        }).format(num);
    } catch {
        return `${currencyCode} ${num.toLocaleString(locale)}`;
    }
}

export function formatDate(
    dateString: string,
    locale = frontendLocale(),
    options?: Intl.DateTimeFormatOptions,
): string {
    if (!dateString) return '';
    try {
        const parts = dateString.split('-');
        const date =
            parts.length === 3
                ? new Date(
                      parseInt(parts[0], 10),
                      parseInt(parts[1], 10) - 1,
                      parseInt(parts[2], 10),
                  )
                : new Date(dateString);

        return new Intl.DateTimeFormat(
            locale,
            options ?? {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            },
        ).format(date);
    } catch {
        return dateString;
    }
}

export function formatTransactionDateTime(
    dateString: string,
    timeString?: string | null,
    locale = frontendLocale(),
): { dateLabel: string; timeLabel?: string } {
    if (!dateString) return { dateLabel: '' };

    try {
        const parts = dateString.split('-');
        if (parts.length === 3) {
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            const target = new Date(y, m, d);

            const now = new Date();
            const today = new Date(
                now.getFullYear(),
                now.getMonth(),
                now.getDate(),
            );
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            let dateLabel = '';
            if (target.getTime() === today.getTime()) {
                dateLabel = t('date.today');
            } else if (target.getTime() === yesterday.getTime()) {
                dateLabel = t('date.yesterday');
            } else if (target.getFullYear() === now.getFullYear()) {
                dateLabel = new Intl.DateTimeFormat(locale, {
                    day: 'numeric',
                    month: 'short',
                }).format(target);
            } else {
                dateLabel = new Intl.DateTimeFormat(locale, {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                }).format(target);
            }

            let timeLabel: string | undefined;
            if (timeString) {
                timeLabel = timeString.slice(0, 5);
            }

            return { dateLabel, timeLabel };
        }

        return { dateLabel: dateString, timeLabel: timeString?.slice(0, 5) };
    } catch {
        return { dateLabel: dateString, timeLabel: timeString?.slice(0, 5) };
    }
}

export function debounce<T extends (...args: any[]) => any>(
    fn: T,
    delay = 350,
): (...args: Parameters<T>) => void {
    let timer: ReturnType<typeof setTimeout> | undefined;
    return (...args: Parameters<T>) => {
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => {
            fn(...args);
        }, delay);
    };
}

export function splitBrandName(name: string): {
    first: string;
    second: string;
    hasSpace: boolean;
} {
    if (!name) return { first: '', second: '', hasSpace: false };

    const spaceIndex = name.indexOf(' ');
    if (spaceIndex !== -1) {
        return {
            first: name.slice(0, spaceIndex),
            second: name.slice(spaceIndex + 1),
            hasSpace: true,
        };
    }

    const match = name.match(/^([A-Z][a-z0-9]+)([A-Z].*)$/);
    if (match) {
        return {
            first: match[1],
            second: match[2],
            hasSpace: false,
        };
    }

    return {
        first: name,
        second: '',
        hasSpace: false,
    };
}
