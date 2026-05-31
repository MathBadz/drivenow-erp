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

export type CustomerStatus = 'active' | 'inactive' | 'blacklisted';
export type CustomerTier = 'regular' | 'silver' | 'gold' | 'platinum';

export type CustomerActivity = {
    id: number;
    type: string;
    description: string;
    created_at: string;
};

export type CustomerFeedback = {
    id: number;
    rating: number;
    comment: string | null;
    created_at: string;
};

export type Customer = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
    city: string | null;
    status: CustomerStatus;
    status_label: string;
    tier: CustomerTier;
    tier_label: string;
    loyalty_points: number;
    total_rentals: number;
    total_spent: number;
    blacklist_reason: string | null;
    joined_at: string | null;
    notes: string | null;
    activities?: CustomerActivity[];
    feedback?: CustomerFeedback[];
    created_at: string;
};

export type CustomerStats = {
    total: number;
    active: number;
    inactive: number;
    blacklisted: number;
};

export type RentalHistoryItem = {
    id: number;
    reference: string;
    vehicle_name: string;
    pickup_date: string;
    return_date: string;
    total: number;
    status: string;
    status_label: string;
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

export type CustomerFilters = { search: string | null; status: string | null; tier: string | null };
export type Option = { value: string; label: string };
