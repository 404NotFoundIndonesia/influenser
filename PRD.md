# PRD — Influenser

**Product**: Influenser  
**Owner**: 404 Not Found Indonesia  
**Last updated**: 2026-05-13

---

## Overview

Influenser is a web-based influencer campaign management platform for brands and agencies. It centralises influencer discovery, campaign creation, engagement tracking, and payment management in one tool — replacing spreadsheets and ad-hoc coordination.

**Primary users**: Marketing managers and campaign coordinators at brands or agencies who run endorsement campaigns with social media creators (KOL = Key Opinion Leaders).

---

## Core Concepts

| Term | Definition |
|------|------------|
| **Influencer** | A person registered in the system. Holds contact info and profile. |
| **KOL (Key Opinion Leader)** | A social media account belonging to an influencer. One influencer can have multiple KOLs across different platforms. |
| **Niche** | A content category (e.g. Fashion, Tech, Food) used to tag and filter influencers. |
| **Campaign** | An endorsement project with a defined period, status, and set of participating influencers. |

---

## Features

### F1 — Authentication & User Management
**Status: Implemented**

- Email/password login and registration
- Email verification
- Password reset via email
- User profile (name, email, avatar)
- Password change from settings
- Appearance settings (light/dark mode)

---

### F2 — Niche Management
**Status: Implemented**

Niches are content categories used to classify influencers.

- Create, edit, delete niches (name, slug, icon, description, active flag)
- Mass delete
- Server-side filtering and pagination

---

### F3 — Influencer (KOL) Management
**Status: Implemented**

- Create influencer with: name, bio, location, phone, WhatsApp, email, status, profile picture
- Attach one or more **KOL accounts** per influencer, each with:
  - Platform (TikTok, Instagram, Facebook, Twitter, YouTube, Pinterest, LinkedIn, Twitch, Discord, Reddit, Threads, Telegram)
  - Username, profile link
  - Metrics: followers, following, total content, views, likes, shares, comments
  - Averages: avg views, avg likes, avg shares, avg comments
  - Engagement rate, endorsement rate (price/post)
  - `synced_at` / `syncing_at` timestamps for external data sync
  - `custom` JSON field for platform-specific extra data
- Tag influencers with multiple niches
- Edit and delete individual KOL accounts
- Influencer status: `active`, `inactive`, `banned`
- Profile picture upload (stored via Laravel Filesystem / S3)
- Server-side filtering (PrimeVue matchMode format) and pagination
- Mass delete

---

### F4 — Campaign Management
**Status: Implemented (core); Engagement linking not yet implemented**

- Create campaign with: name, description, start date, end date, status, banner image
- Campaign status workflow: `draft` → `ongoing` → `completed` / `cancelled`
- Auto-generated slug from name
- Banner image upload
- Server-side filtering and pagination
- Mass delete

**Not yet implemented:**
- Linking influencers/KOLs to campaigns
- Per-campaign engagement tracking (views, reach, conversions per KOL)
- Campaign timeline/calendar view

---

### F5 — Engagement Tracking
**Status: Not implemented**

Track how each KOL performs within a campaign.

- Attach KOLs to a campaign with a defined role/deliverable
- Record actual post metrics per KOL per campaign (reach, impressions, engagements)
- Compare contracted vs. actual performance
- Campaign-level aggregate metrics

**Required schema additions:**
- `campaign_key_opinion_leader` pivot table: `campaign_id`, `key_opinion_leader_id`, `deliverable`, `posted_at`, `actual_views`, `actual_likes`, `actual_comments`, `actual_shares`

---

### F6 — Notifications & Reminders
**Status: Not implemented**

- Email notifications to influencers (campaign brief, payment confirmation)
- Internal reminders for campaign milestones (start date, deadline)
- Notification channel: SMTP (already configured in Laravel mail stack)

---

### F7 — Invoices & Payments
**Status: Not implemented**

Track compensation owed to influencers per campaign.

- Generate invoice per KOL per campaign based on `endorsement_rate` × deliverable count
- Payment status: `unpaid`, `pending`, `paid`
- Attach proof of payment
- Export invoice as PDF

**Required schema additions:**
- `invoices` table: `campaign_id`, `influencer_id`, `amount`, `status`, `paid_at`, `notes`

---

### F8 — Dashboard Analytics
**Status: Scaffolded only — no real data**

- Total influencers, campaigns, active campaigns
- Top-performing influencers by engagement rate
- Campaign status breakdown
- Recent activity feed

---

### F9 — CreatorDB Integration
**Status: Service layer implemented; UI not connected**

`CreatorDBServiceInterface` and `CreatorDBServiceV1` are implemented. The service can fetch profile data and history for TikTok, Instagram, YouTube, and Facebook accounts.

**Not yet implemented:**
- UI to search and import influencers from CreatorDB
- Auto-populate KOL metrics from CreatorDB on influencer create/edit
- Trigger manual refresh of KOL data from CreatorDB (uses `synced_at` / `syncing_at` on the KOL record)

**Config**: `CREATOR_DB_API` + `CREATOR_DB_URL` env vars → `config/influenser.php`

---

### F10 — Apify Scraper Integration
**Status: Not implemented**

Scrape live social media metrics via Apify actors as an alternative/supplement to CreatorDB.

- Connect to Apify API
- Run per-platform actors to fetch follower count, engagement rate, recent post metrics
- Write results back to the KOL's metric columns + set `synced_at`
- Should use the same `synced_at` / `syncing_at` sync-state pattern already on the KOL model

---

## Data Model (current)

```
users
influencers           (uuid, name, bio, location, phone, whatsapp, email, status, profile_picture_path)
key_opinion_leaders   (uuid, influencer_id, username, platform, link, bio, engagement_rate,
                       followers, following, total_content, views, likes, shares, comments,
                       avg_views, avg_likes, avg_shares, avg_comments, endorsement_rate,
                       custom json, synced_at, syncing_at)
niches                (uuid, name, slug, icon, description, active)
influencer_niche      (influencer_id, niche_id)  — pivot
campaigns             (uuid, name, slug, description, start_date, end_date, status, banner_path)
```

---

## Platforms Supported

TikTok, Instagram, Facebook, Twitter, YouTube, Pinterest, LinkedIn, Twitch, Discord, Reddit, Threads, Telegram

---

## Tech Constraints

- Backend: Laravel 12, PHP 8.2+
- Frontend: Vue 3 + TypeScript via Inertia.js (no separate API layer)
- Database: PostgreSQL (SQLite for tests)
- Storage: Laravel Filesystem (local or S3)
- All models use UUIDs as primary keys
- Filtering uses PrimeVue DataTable `matchMode` format — handled by the `Filterable` trait
