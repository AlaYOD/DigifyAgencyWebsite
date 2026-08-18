export interface CareerJob {
    id: number;
    slug: string;
    title: string;
    summary: string;
    description: string;
    responsibilities: string;
    requirements: string;
    benefits: string;
    reference_code: string;
    department: {
        name: string;
        sort_order: number;
    };
    employment_type: string;
    workplace_type: string;
    city: string | null;
    country_code: string | null;
    salary_min: number | null;
    salary_max: number | null;
    salary_currency: string | null;
    salary_period: string | null;
    salary_is_public: boolean;
    published_at: string;
    closes_at: string | null;
    relative_published_at: string;
    json_ld?: Record<string, unknown>;
    meta: {
        title: string;
        description: string;
        canonical: string;
        hreflang: {
            en: string;
            ar: string;
            'x-default': string;
        };
    };
}
