/**
 * Shared formatting helpers. Every DriveNow service formats money and dates
 * identically (see DESIGN_STANDARDS §17 — always en-US / USD).
 */

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
});

const numberFmt = new Intl.NumberFormat('en-US');

/** Format a numeric value as USD currency, e.g. `1234.5` → `$1,234.50`. */
export function formatCurrency(value: number | string | null | undefined): string {
    const num = typeof value === 'string' ? Number(value) : (value ?? 0);

    return currency.format(Number.isFinite(num) ? (num as number) : 0);
}

/** Format an integer/float with thousands separators, e.g. `12000` → `12,000`. */
export function formatNumber(value: number | string | null | undefined): string {
    const num = typeof value === 'string' ? Number(value) : (value ?? 0);

    return numberFmt.format(Number.isFinite(num) ? (num as number) : 0);
}

/** Format a date string as `Jan 5, 2026`. */
export function formatDate(value: string | Date | null | undefined): string {
    if (!value) {
        return '—';
    }

    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

/** Format a date-time string as `Jan 5, 2026, 2:30 PM`. */
export function formatDateTime(value: string | Date | null | undefined): string {
    if (!value) {
        return '—';
    }

    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}
