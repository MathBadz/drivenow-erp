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

export type MaintenanceStatus = 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
export type MaintenanceType = 'inspection' | 'repair' | 'scheduled' | 'damage';
export type MaintenanceSeverity = 'low' | 'medium' | 'high';

export type MaintenanceRecord = {
    id: number;
    reference: string;
    vehicle_id: number | null;
    vehicle_name: string;
    vehicle_plate: string;
    type: MaintenanceType;
    type_label: string;
    status: MaintenanceStatus;
    status_label: string;
    severity: MaintenanceSeverity;
    severity_label: string;
    title: string;
    description: string | null;
    cost: number;
    odometer: number | null;
    scheduled_date: string | null;
    completed_date: string | null;
    notes: string | null;
    created_at: string;
};

export type MaintenanceStats = {
    total: number;
    scheduled: number;
    in_progress: number;
    completed: number;
    cancelled: number;
    total_cost: number;
    downtime: number;
};

export type FleetVehicle = { id: number; name: string; plate_number: string };

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

export type MaintenanceFilters = { search: string | null; status: string | null; type: string | null };
export type Option = { value: string; label: string };
