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

export type Kpis = {
    revenue: number;
    rentals: number;
    utilization: number;
    active_customers: number;
    avg_rental_value: number;
    maintenance_cost: number;
};

export type MonthlyRevenue = { month: string; revenue: number; rentals: number };
export type MaintenanceMonthly = { month: string; cost: number };
export type TopVehicle = { name: string; plate: string; rentals: number; revenue: number };

export type Utilization = {
    percent: number;
    rented: number;
    available: number;
    maintenance: number;
    total: number;
};

export type Retention = { percent: number; returning: number; new: number };
export type PaymentMix = { cash: number; gcash: number; card: number };

/** Data freshness: which sibling services were unreachable on the last build. */
export type AnalyticsMeta = { live: boolean; degraded: string[] };

export type ReportRow = {
    date: string;
    rentals: number;
    revenue: number;
    returns: number;
    maintenance_cost: number;
};
