import { formatCurrency } from './utils';
import { frontendLocale } from './i18n';

export class BalanceVisibilityManager {
    isHidden = $state<boolean>(false);

    constructor() {
        if (typeof window !== 'undefined') {
            const saved = localStorage.getItem('catatduit_hide_balance');
            this.isHidden = saved === 'true';
        }
    }

    toggle(): boolean {
        this.isHidden = !this.isHidden;
        if (typeof window !== 'undefined') {
            localStorage.setItem(
                'catatduit_hide_balance',
                String(this.isHidden),
            );
        }
        return this.isHidden;
    }

    hide(): void {
        this.isHidden = true;
        if (typeof window !== 'undefined') {
            localStorage.setItem('catatduit_hide_balance', 'true');
        }
    }

    show(): void {
        this.isHidden = false;
        if (typeof window !== 'undefined') {
            localStorage.setItem('catatduit_hide_balance', 'false');
        }
    }

    /**
     * Formats an amount with currency according to current visibility state.
     * When hidden, returns masked placeholder (e.g. 'Rp ********' or 'USD ********').
     */
    format(
        amount: number | string,
        currencyCode = 'IDR',
        locale = frontendLocale(),
    ): string {
        if (this.isHidden) {
            return currencyCode === 'IDR'
                ? 'Rp ********'
                : `${currencyCode} ********`;
        }
        return formatCurrency(amount, currencyCode, locale);
    }

    /**
     * Formats an amount with leading sign (+ or -) and currency according to visibility.
     */
    formatSigned(
        amount: number | string,
        type: 'income' | 'expense' | 'net',
        currencyCode = 'IDR',
        locale = frontendLocale(),
    ): string {
        const sign =
            type === 'income'
                ? '+'
                : type === 'expense'
                  ? '-'
                  : Number(amount) >= 0
                    ? '+'
                    : '';
        if (this.isHidden) {
            return `${sign}${currencyCode === 'IDR' ? 'Rp ********' : `${currencyCode} ********`}`;
        }
        return `${sign}${formatCurrency(amount, currencyCode, locale)}`;
    }
}

export const balanceVisibility = new BalanceVisibilityManager();
