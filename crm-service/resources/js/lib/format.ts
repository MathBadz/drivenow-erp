/**
 * Shared formatting helpers — every DriveNow service formats money and dates
 * identically (DESIGN_STANDARDS §17 — always en-US / USD).
 */

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
});

const numberFmt = new Intl.NumberFormat('en-US');

export function formatCurrency(value: number | string | null | undefined): string {
    const num = typeof value === 'string' ? Number(value) : (value ?? 0);
    return currency.format(Number.isFinite(num) ? (num as number) : 0);
}

export function formatNumber(value: number | string | null | undefined): string {
    const num = typeof value === 'string' ? Number(value) : (value ?? 0);
    return numberFmt.format(Number.isFinite(num) ? (num as number) : 0);
}

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
