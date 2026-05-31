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

export type StoreVehicle = {
    id: number;
    name: string;
    make?: string;
    model?: string;
    plate_number?: string;
    category?: string;
    category_label?: string;
    status?: string;
    daily_rate: number;
    seats?: number;
    transmission?: string;
    fuel_type?: string;
};

export type BookingStatus =
    | 'pending'
    | 'confirmed'
    | 'active'
    | 'completed'
    | 'cancelled';

export type Booking = {
    id: number;
    reference: string;
    vehicle_id: number | null;
    vehicle_name: string;
    vehicle_category: string | null;
    vehicle_plate: string | null;
    daily_rate: number;
    pickup_date: string;
    return_date: string;
    days: number;
    total: number;
    status: BookingStatus;
    notes: string | null;
    created_at: string;
};

export type BookingStats = {
    total: number;
    active: number;
    completed: number;
    spent: number;
};
