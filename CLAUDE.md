# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

**Start dev environment** (Laravel serve + queue + pail logs + Vite, all at once):
```bash
composer run dev
```

**Individual processes:**
```bash
php artisan serve
npm run dev
php artisan queue:listen --tries=1
```

**Build:**
```bash
npm run build
npm run build:ssr   # SSR build + Inertia SSR server
```

**Tests** (uses in-memory SQLite):
```bash
php artisan test
./vendor/bin/pest
php artisan test --filter=DashboardTest   # single test
```

**Linting & formatting:**
```bash
npm run lint              # ESLint (auto-fix)
npm run format            # Prettier (auto-fix)
npm run format:check      # Prettier (check only)
./vendor/bin/pint         # PHP code style (Laravel Pint)
```

**Database:**
```bash
php artisan migrate --seed
php artisan db:seed --class=DummySeeder   # optional dummy data
php artisan storage:link
```

## Architecture

### Stack
Laravel 12 backend serving a Vue 3 SPA via **Inertia.js**. No separate API — controllers return `Inertia::render()` calls directly. Frontend is TypeScript + TailwindCSS.

### Controller structure
Controllers live under `App\Http\Controllers\Web\` with sub-namespaces:
- `Auth\` — standard Laravel auth (session-based)
- `Essential\` — supporting entities (NicheController)
- `Influencer\` — InfluencerController, KeyOpinionLeaderController
- `Campaign\` — CampaignController
- `Settings\` — ProfileController, PasswordController

Routes are in `routes/web.php` (main), `routes/auth.php`, `routes/settings.php`. All main routes require `auth` + `verified` middleware.

### Models & traits
All primary models use UUIDs (`HasUuids`). Custom traits in `app/Traits/Models/`:

- **`Filterable`** — `scopeFilter()` accepts a PrimeVue DataTable filter array (`[column => [value, matchMode]]`). Supports dot-notation for relation filters (`niches.name`). Match modes: `contains`, `equals`, `startsWith`, `endsWith`, `in`, `between`, `dateIs`, etc.
- **`HasPicture`** — file upload/delete helpers; set `$picturePathColumn` in model constructor.
- **`HasSlug`** — auto-generates slugs.
- **`Paginate`** — `scopePaginate()` wrapper.

Model relationships:
- `Influencer` → hasMany `KeyOpinionLeader` (KOL), belongsToMany `Niche` (pivot: `influencer_niche`)
- `Campaign` is standalone

### Third-party integration: CreatorDB
`CreatorDBServiceInterface` / `CreatorDBServiceV1` in `app/Services/ThirdParty/` fetch influencer data from CreatorDB API (TikTok, Instagram, YouTube, Facebook). Bound as singleton in `ThirdPartyServiceProvider`. Config in `config/influenser.php`, env vars: `CREATOR_DB_API`, `CREATOR_DB_URL`.

### Frontend structure
- `resources/js/pages/` — Inertia page components, mirroring route groups (`campaign/`, `influencer/`, `niche/`, `auth/`, `settings/`)
- `resources/js/components/ui/` — shadcn-vue primitives (radix-vue based)
- `resources/js/components/` — app-level components (AppSidebar, AppHeader, NavMain, etc.)
- `resources/js/layouts/` — AppLayout (sidebar), AuthLayout, settings Layout
- `resources/js/types/model.ts` — TypeScript interfaces matching PHP models; enums for `Platform`, `InfluencerStatus`, `CampaignStatus`
- `@` alias resolves to `resources/js/`

### UI libraries
- **shadcn-vue** (radix-vue) for base primitives — components in `resources/js/components/ui/`
- **PrimeVue** for data-heavy components (DataTable with server-side filtering)
- **Lucide Vue Next** for icons
- Tailwind CSS v3 for styling

### Form requests
Validation lives in `app/Http/Requests/` per domain (`Campaign/`, `Influencer/`, `Niche/`, `Settings/`, `Auth/`).
