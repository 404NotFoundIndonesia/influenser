
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

export enum InvoiceStatus {
    Unpaid = 'unpaid',
    Pending = 'pending',
    Paid = 'paid',
}

export enum CampaignStatus {
    Draft = 'draft',
    Ongoing = 'ongoing',
    Completed = 'completed',
    Cancelled = 'cancelled',
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

// No reference to Influencer here — avoids circular dependency.
// Use CampaignKeyOpinionLeader when influencer data is needed.
export interface KeyOpinionLeader {
    id: string;
    username: string;
    platform: Platform;
    platform_name: string;
    link: string;
    bio: string | null;
    engagement_rate: number;
    followers: number;
    following: number;
    total_content: number;
    views: number;
    likes: number;
    shares: number;
    comments: number;
    avg_views: number;
    avg_likes: number;
    avg_shares: number;
    avg_comments: number;
    endorsement_rate: number;
    is_syncing: boolean;
    synced_at: string | null;
    syncing_at: string | null;
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
    key_opinion_leaders?: KeyOpinionLeader[];
    niches?: Niche[];
    created_at: Date;
    updated_at: Date;
}

export interface CampaignKolPivot {
    deliverable: string | null;
    posted_at: string | null;
    actual_views: number | null;
    actual_likes: number | null;
    actual_comments: number | null;
    actual_shares: number | null;
}

export interface CampaignKeyOpinionLeader extends KeyOpinionLeader {
    pivot: CampaignKolPivot;
    influencer?: Influencer;
}

export interface Invoice {
    id: string;
    campaign_id: string;
    influencer_id: string;
    key_opinion_leader_id: string | null;
    amount: string;
    status: InvoiceStatus;
    paid_at: string | null;
    proof_path: string | null;
    picture_url: string;
    notes: string | null;
    influencer?: Influencer;
    key_opinion_leader?: KeyOpinionLeader;
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
    key_opinion_leaders?: CampaignKeyOpinionLeader[];
    invoices?: Invoice[];
    created_at: Date;
    updated_at: Date;
}
