export type SystemSettings = {
    business_name: string;
    business_email: string | null;
    business_phone: string | null;
    business_address: string | null;
    business_website: string | null;
    business_description: string | null;
    logo_url: string | null;
    favicon_url: string | null;
    currency: string;
    currency_symbol: string;
    timezone: string;
};

export type InvoiceStatus = 'unpaid' | 'partial' | 'paid' | 'overdue' | 'refunded';
export type PaymentMethod = 'cash' | 'gcash' | 'card';

export type Payment = {
    id: number;
    method: PaymentMethod;
    method_label: string;
    amount: number;
    reference: string | null;
    paid_at: string | null;
};

export type Invoice = {
    id: number;
    invoice_number: string;
    customer_name: string;
    customer_email: string | null;
    rental_reference: string | null;
    subtotal: number;
    penalty: number;
    discount: number;
    total: number;
    amount_paid: number;
    balance: number;
    status: InvoiceStatus;
    status_label: string;
    due_date: string | null;
    issued_at: string | null;
    notes: string | null;
    payments?: Payment[];
    created_at: string;
};

export type InvoiceStats = {
    total: number;
    unpaid: number;
    partial: number;
    paid: number;
    overdue: number;
    refunded: number;
    invoiced: number;
    collected: number;
    outstanding: number;
    overdue_amount: number;
};

export type PaginationLink = { url: string | null; label: string; active: boolean };

export type Paginated<T> = {
    data: T[];
    links: { first: string | null; last: string | null; prev: string | null; next: string | null };
    meta: {
        current_page: number;
        from: number | null;
        to: number | null;
        total: number;
        last_page: number;
        per_page: number;
        links: PaginationLink[];
    };
};

export type InvoiceFilters = { search: string | null; status: string | null };
export type Option = { value: string; label: string };
