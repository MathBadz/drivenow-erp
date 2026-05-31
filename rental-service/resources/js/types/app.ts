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

export type RentalStatus =
    | 'pending'
    | 'approved'
    | 'active'
    | 'completed'
    | 'cancelled';

export type Rental = {
    id: number;
    reference: string;
    customer_name: string;
    customer_email: string | null;
    customer_phone: string | null;
    vehicle_id: number | null;
    vehicle_name: string;
    vehicle_plate: string;
    pickup_branch: string;
    pickup_date: string;
    return_date: string;
    days: number;
    daily_rate: number;
    subtotal: number;
    total: number;
    status: RentalStatus;
    status_label: string;
    notes: string | null;
    approved_at: string | null;
    released_at: string | null;
    returned_at: string | null;
    cancelled_at: string | null;
    created_at: string;
    updated_at: string;
};

export type RentalStats = {
    total: number;
    pending: number;
    approved: number;
    active: number;
    completed: number;
    cancelled: number;
    revenue: number;
};

export type AvailableVehicle = {
    id: number;
    name: string;
    plate_number: string;
    category_label: string;
    daily_rate: number;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
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

export type RentalFilters = {
    search: string | null;
    status: string | null;
};

export type Option = {
    value: string;
    label: string;
};
