
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

export enum Platform {
    TikTok = 'tiktok',
    Instagram = 'instagram',
    Facebook = 'facebook',
    Twitter = 'twitter',
    Pinterest = 'pinterest',
    Youtube = 'youtube',
    LinkedIn = 'linkedin',
    Twitch = 'twitch',
    Discord = 'discord',
    Reddit = 'reddit',
    Threads = 'threads',
    Telegram = 'telegram',
}

export enum CampaignStatus {
    Draft = 'draft',
    Ongoing = 'ongoing',
    Completed = 'completed',
    Cancelled = 'cancelled',
}

export interface KeyOpinionLeader {
    id: string;
    platform: Platform;
    link: string;
    engagement_rate: number;
    followers: number;
    following: number;
    views: number;
    likes: number;
    shares: number;
    comments: number;
    avg_views: number;
    avg_likes: number;
    avg_shares: number;
    avg_comments: number;
    endorsement_rate: number;
    created_at: Date;
    updated_at: Date;
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
    picture_url: string|null;
    key_opinion_leaders?: KeyOpinionLeader[] | any[];
    niches?: Niche[],
    created_at: Date;
    updated_at: Date;
}

export interface Niche {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    icon: string;
    active: boolean;
    created_at: Date;
    updated_at: Date;
}

export interface Campaign {
    id: string;
    name: string;
    description: string;
    start_date: Date | null;
    end_date: Date | null;
    status: CampaignStatus;
    banner_path: string | null;
    picture_url: string;
    created_at: Date;
    updated_at: Date;
}
