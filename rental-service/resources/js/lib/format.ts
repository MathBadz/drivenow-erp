/**
 * Shared formatting helpers. The currency is driven by the dynamic system
 * settings broadcast from auth-service (configured at startup + on every
 * visit by lib/flashToast.ts), so it is NOT hardcoded — change the currency
 * in the Operations Hub and every service follows.
 */

const locale = 'en-US';
let currencyCode = 'USD';
let currencySymbol: string | null = null;

function buildCurrencyFmt(code: string): Intl.NumberFormat {
    try {
        return new Intl.NumberFormat(locale, { style: 'currency', currency: code });
    } catch {
        return new Intl.NumberFormat(locale, { style: 'currency', currency: 'USD' });
    }
}

// Cached formatters — rebuilt only when the currency changes, not per call.
const numberFmt = new Intl.NumberFormat(locale);
const decimalFmt = new Intl.NumberFormat(locale, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
let currencyFmt = buildCurrencyFmt(currencyCode);

/**
 * Set the active currency from system settings. `code` is the ISO 4217 code
 * (e.g. 'USD', 'PHP'); `symbol` is the admin-chosen display symbol — when
 * provided it overrides the locale's default glyph (e.g. show '₱' for PHP).
 */
export function configureCurrency(code?: string | null, symbol?: string | null): void {
    if (code && code.length === 3) {
        currencyCode = code.toUpperCase();
        currencyFmt = buildCurrencyFmt(currencyCode);
    }
    currencySymbol = symbol && symbol.trim() !== '' ? symbol.trim() : null;
}

function toSafe(value: number | string | null | undefined): number {
    const num = typeof value === 'string' ? Number(value) : (value ?? 0);
    return Number.isFinite(num) ? (num as number) : 0;
}

export function formatCurrency(value: number | string | null | undefined): string {
    const safe = toSafe(value);
    // Honor an explicit admin-configured symbol; otherwise use the ISO format.
    if (currencySymbol) {
        return `${currencySymbol}${decimalFmt.format(safe)}`;
    }
    return currencyFmt.format(safe);
}

export function formatNumber(value: number | string | null | undefined): string {
    return numberFmt.format(toSafe(value));
}

export function formatDate(value: string | Date | null | undefined): string {
    if (!value) {
        return '—';
    }
    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '—';
    }
    return date.toLocaleDateString(locale, { month: 'short', day: 'numeric', year: 'numeric' });
}

export function formatDateTime(value: string | Date | null | undefined): string {
    if (!value) {
        return '—';
    }
    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '—';
    }
    return date.toLocaleString(locale, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}
