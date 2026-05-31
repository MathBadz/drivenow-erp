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

export type VehicleStatus =
    | 'available'
    | 'reserved'
    | 'rented'
    | 'maintenance'
    | 'inactive';

export type VehicleCategory =
    | 'sedan'
    | 'hatchback'
    | 'suv'
    | 'van'
    | 'pickup';

export type Vehicle = {
    id: number;
    name: string;
    make: string;
    model: string;
    year: number;
    plate_number: string;
    category: VehicleCategory;
    category_label: string;
    status: VehicleStatus;
    status_label: string;
    branch: string;
    daily_rate: number;
    seats: number;
    transmission: string;
    fuel_type: string;
    color: string | null;
    mileage: number;
    image_url: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
};

export type VehicleStats = {
    total: number;
    available: number;
    reserved: number;
    rented: number;
    maintenance: number;
    inactive: number;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

/** Matches Laravel's paginated API-resource collection JSON. */
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

export type VehicleFilters = {
    search: string | null;
    status: string | null;
    category: string | null;
};

export type Option = {
    value: string;
    label: string;
};
