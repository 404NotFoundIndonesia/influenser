
export interface Link {
    url: string|null;
    label: string;
    active: boolean;
}

export interface Paginate<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number|null;
    last_page: number;
    last_page_url: string;
    links: Link[];
    next_page_url: string|null;
    path: string;
    per_page: number;
    prev_page_url: string|null;
    to: number|null;
    total: number;
}


export enum InfluencerStatus {
    Active = "active",
    Inactive = "inactive",
    Banned = "banned",
}

export interface Influencer {
    id: string;
    name: string;
    bio: string|null;
    location: string|null;
    phone: string|null;
    whatsapp: string|null;
    email: string|null;
    status: InfluencerStatus;
    profile_picture_path: string|null;
    created_at: string|null;
    updated_at: string|null;
    picture_url: string|null;
}
