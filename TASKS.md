# TASKS.md — Influenser

Tracks all remaining implementation work derived from [PRD.md](PRD.md).

**Reading the task IDs**: `T{section}.{task}` — e.g. T1.3 = Section 1, Task 3.

**Critical path**: Section 1 (campaign-KOL linking) must be done first. Section 2 (engagement) and Section 4 (invoices) depend on it. Section 5 (dashboard) depends on Sections 2 and 4. Sections 3, 6, and 7 are independent.

**Testing convention**: Tests live in `tests/Feature/` (HTTP + DB) or `tests/Unit/` (isolated logic). All tests use Pest. Frontend Vue pages have no PHP tests — note "no backend test" where applicable.

---

## Section 1 — Campaign–KOL Linking
> Completes F4. Prerequisite for Sections 2, 4, and 5.

- [x] **T1.1 — Migration: `campaign_key_opinion_leader` pivot table**
  - Create migration with columns: `campaign_id` (uuid FK → campaigns), `key_opinion_leader_id` (uuid FK → key_opinion_leaders), `deliverable` (string, nullable), `posted_at` (timestamp, nullable), `actual_views`, `actual_likes`, `actual_comments`, `actual_shares` (unsignedBigInteger, nullable each).
  - **DoD**: `php artisan migrate` and `php artisan migrate:rollback` both run cleanly. Table appears in DB with correct FK constraints.
  - **Test**: Covered implicitly by T1.2 and T1.4 feature tests which run against in-memory SQLite.

- [x] **T1.2 — Model: `Campaign::keyOpinionLeaders()` relationship**
  - Add `belongsToMany(KeyOpinionLeader::class)` on `Campaign` model through the pivot. Expose pivot extras (`deliverable`, `posted_at`, `actual_*`) via `withPivot()`.
  - **DoD**: `Campaign::with('keyOpinionLeaders')->first()->keyOpinionLeaders` returns a collection; `->pivot->deliverable` is accessible.
  - **Test**: `tests/Feature/Campaign/CampaignKolRelationshipTest.php`
    - Assert `keyOpinionLeaders()` returns a `BelongsToMany` instance.
    - Create a campaign + KOL, attach them with a deliverable, assert `$campaign->keyOpinionLeaders->first()->pivot->deliverable` equals the value set.

- [x] **T1.3 — Model: `KeyOpinionLeader::campaigns()` reverse relationship**
  - Add `belongsToMany(Campaign::class)` on `KeyOpinionLeader` with same pivot extras.
  - **DoD**: `KeyOpinionLeader::with('campaigns')->first()->campaigns` returns a collection.
  - **Test**: Same file as T1.2 — assert reverse: attach KOL to campaign, then load `$kol->campaigns` and confirm campaign is present.

- [x] **T1.4 — Backend: attach/detach KOL endpoints**
  - Create `App\Http\Controllers\Web\Campaign\CampaignKolController` with:
    - `store(Request $request, Campaign $campaign)` — validates `key_opinion_leader_id` + optional `deliverable`, attaches via `syncWithoutDetaching`, returns redirect back.
    - `destroy(Campaign $campaign, KeyOpinionLeader $kol)` — detaches from pivot.
  - Register routes: `POST /campaign/{campaign}/kol`, `DELETE /campaign/{campaign}/kol/{kol}`.
  - **DoD**: Both endpoints work; attaching a KOL already on the campaign does not duplicate the pivot row. `422` returned if `key_opinion_leader_id` missing.
  - **Test**: `tests/Feature/Campaign/CampaignKolControllerTest.php`
    - `store` → assert pivot row created, redirect back.
    - `store` again with same KOL → assert still only 1 pivot row (no duplicate).
    - `store` with missing `key_opinion_leader_id` → assert 422.
    - `destroy` → assert pivot row removed; KOL record itself untouched.
    - All requests as authenticated user; assert 302 redirect for unauthenticated.

- [x] **T1.5 — Frontend: KOL panel on Campaign Show page**
  - Add a "KOLs" section to `resources/js/pages/campaign/Show.vue`.
  - Displays a table of attached KOLs: influencer name, platform badge, username, deliverable, posted date, action to detach.
  - Includes a search-and-attach form: select an influencer → select one of their KOL accounts → enter deliverable → submit.
  - Pass `keyOpinionLeaders` (eager-loaded with influencer) and `allInfluencers` (or a lazy search endpoint) from `CampaignController::show`.
  - **DoD**: User can attach a KOL with a deliverable, see it in the table, and detach it — all without page reload (Inertia).
  - **Test**: `tests/Feature/Campaign/CampaignShowTest.php` — assert `campaign.show` Inertia response includes `keyOpinionLeaders` prop with correct structure. No Vue test.

---

## Section 2 — Engagement Tracking
> Implements F5. Depends on Section 1 (pivot table must exist).

- [x] **T2.1 — Backend: update engagement metrics on pivot**
  - Add `update(Request $request, Campaign $campaign, KeyOpinionLeader $kol)` to `CampaignKolController`.
  - Validates and saves: `actual_views`, `actual_likes`, `actual_comments`, `actual_shares`, `posted_at`.
  - Route: `PUT /campaign/{campaign}/kol/{kol}`.
  - **DoD**: Pivot row updates correctly. Fields can be individually nullable (partial reporting allowed).
  - **Test**: `tests/Feature/Campaign/CampaignKolControllerTest.php` (same file as T1.4)
    - Attach KOL to campaign, then PUT with actual metrics → assert pivot fields updated in DB.
    - PUT with only `posted_at` (others null) → assert only `posted_at` changed, others remain null.
    - PUT with non-attached KOL → assert 404.

- [x] **T2.2 — Frontend: per-KOL engagement input on Campaign Show**
  - Extend the KOL row (from T1.5) with an inline edit or modal: input fields for actual metrics and posted date.
  - Show two columns side by side: "Contracted" (KOL's `endorsement_rate`, formatted) and "Actual" (sum of actual metrics).
  - Submit calls the T2.1 endpoint.
  - **DoD**: User can enter actual post metrics for each KOL; data persists and reflects correctly on page reload.
  - **Test**: No backend test. Covered by T2.1 feature test.

- [x] **T2.3 — Frontend: campaign-level engagement summary card**
  - On Campaign Show, add a summary card above the KOL table.
  - Shows: total KOLs attached, total posted (count where `posted_at` not null), sum of `actual_views`, sum of `actual_likes`.
  - **DoD**: Card values recalculate from the data already passed to the page; no extra query needed.
  - **Test**: Assert `campaign.show` Inertia props include aggregated summary values (extend `CampaignShowTest.php`).

---

## Section 3 — Notifications & Reminders
> Implements F6. Independent of Sections 1–2, but T3.4 must be wired after T1.4 and T4.3 are done.

- [ ] **T3.1 — Notification classes**
  - `App\Notifications\CampaignBriefNotification` — implements `toMail()`. Accepts a `Campaign` and `KeyOpinionLeader`. Sends to influencer's email.
  - `App\Notifications\PaymentConfirmedNotification` — implements `toMail()`. Accepts an `Invoice`. Sends to influencer's email.
  - **DoD**: Both classes exist and can be dispatched via `Notification::send()` or `$notifiable->notify()`. Confirmed with `php artisan tinker`.
  - **Test**: `tests/Unit/Notifications/CampaignBriefNotificationTest.php` and `PaymentConfirmedNotificationTest.php`
    - Assert `toMail()` returns a `MailMessage` with the correct subject line.
    - Assert mail body contains campaign name / invoice amount respectively.
    - Assert notification targets the correct email address.

- [ ] **T3.2 — Email Blade templates**
  - `resources/views/mail/campaign-brief.blade.php`: campaign name, description, start/end dates, deliverable, KOL platform.
  - `resources/views/mail/payment-confirmed.blade.php`: invoice amount, campaign name, KOL, paid date.
  - Use Laravel's `markdown` mailable format for consistent styling.
  - **DoD**: Templates render without errors. Preview accessible via `Route::get('/mailable/brief', ...)` in local env.
  - **Test**: Covered by T3.1 unit tests (notification renders the template; any Blade error surfaces there).

- [ ] **T3.3 — Scheduled milestone reminder jobs**
  - `App\Jobs\CampaignStartReminderJob` — notifies campaign owner 1 day before `start_date` via a new `CampaignStartReminderNotification`.
  - `App\Jobs\CampaignDeadlineReminderJob` — notifies campaign owner 1 day before `end_date`.
  - Register both in `routes/console.php` using `Schedule::call()->daily()`, querying campaigns where the relevant date is tomorrow.
  - **DoD**: Running `php artisan schedule:run` dispatches jobs for matching campaigns. Job sends notification to the auth user. Verifiable with `MAIL_MAILER=log`.
  - **Test**: `tests/Unit/Jobs/CampaignStartReminderJobTest.php`
    - Create a campaign with `start_date = tomorrow`. Call `handle()`. Assert `Notification::assertSentTo` the campaign owner.
    - Create a campaign with `start_date = 3 days away`. Assert no notification sent.
    - Mirror for `CampaignDeadlineReminderJob`.

- [ ] **T3.4 — Auto-dispatch on key events**
  - In `CampaignKolController::store` (T1.4): after attaching a KOL, if the influencer has an email, dispatch `CampaignBriefNotification`.
  - In `InvoiceController::update` (T4.3): after status changes to `paid`, dispatch `PaymentConfirmedNotification`.
  - **DoD**: Email triggered automatically in both flows. No notification sent if influencer has no email. *(Wire this last — depends on T1.4 and T4.3 both being done.)*
  - **Test**: Extend `CampaignKolControllerTest.php`:
    - Attach KOL to campaign where influencer has email → assert `Notification::assertSentTo` influencer.
    - Attach KOL where influencer has no email → assert `Notification::assertNothingSent`.
    - Extend `InvoiceControllerTest.php` (from T4.3): update invoice status to `paid` → assert `PaymentConfirmedNotification` sent.

---

## Section 4 — Invoices & Payments
> Implements F7. Depends on Section 1 (campaign-KOL pivot). T3.4 wires into T4.3.

- [ ] **T4.1 — Migration: `invoices` table**
  - Columns: `id` (uuid PK), `campaign_id` (uuid FK → campaigns), `influencer_id` (uuid FK → influencers), `key_opinion_leader_id` (uuid FK → key_opinion_leaders, nullable), `amount` (decimal 10,2), `status` (string, default `unpaid`), `paid_at` (timestamp, nullable), `proof_path` (string, nullable), `notes` (text, nullable), timestamps.
  - **DoD**: Migration runs and rolls back cleanly. FK constraints enforce referential integrity.
  - **Test**: Covered by T4.3 feature tests which create invoice records against in-memory SQLite.

- [ ] **T4.2 — Invoice model**
  - `App\Models\Invoice` with `HasUuids`, `Filterable`, `Paginate`, `HasPicture` (proof upload via `proof_path`).
  - Relationships: `belongsTo Campaign`, `belongsTo Influencer`, `belongsTo KeyOpinionLeader`.
  - Status enum: `App\Enum\InvoiceStatus` with cases `Unpaid`, `Pending`, `Paid`.
  - **DoD**: Model exists; `Invoice::with(['campaign','influencer','keyOpinionLeader'])->first()` resolves all relations.
  - **Test**: `tests/Feature/Invoice/InvoiceModelTest.php`
    - Create invoice with related models. Assert all three `belongsTo` relationships return the correct model instances.
    - Assert `InvoiceStatus` enum has exactly the three expected cases.

- [ ] **T4.3 — Backend: Invoice CRUD controller**
  - `App\Http\Controllers\Web\Campaign\InvoiceController` with `index`, `store`, `update`, `destroy`.
  - Routes nested under campaign: `GET /campaign/{campaign}/invoice`, `POST /campaign/{campaign}/invoice`, `PUT /campaign/{campaign}/invoice/{invoice}`, `DELETE /campaign/{campaign}/invoice/{invoice}`.
  - `store`: auto-calculate `amount` from `keyOpinionLeader.endorsement_rate` × deliverable count (from pivot); user can override.
  - `update`: when `status` changes to `paid`, set `paid_at = now()`, handle proof file upload.
  - **DoD**: All CRUD operations work. Amount pre-fills from KOL rate. Paid invoice gets `paid_at` timestamp.
  - **Test**: `tests/Feature/Invoice/InvoiceControllerTest.php`
    - `store` → assert invoice created, `amount` equals `endorsement_rate` when not overridden.
    - `store` with explicit `amount` override → assert stored amount is the override value.
    - `update` with `status = paid` → assert `paid_at` is set, `status` is `paid`.
    - `update` with `status = pending` → assert `paid_at` remains null.
    - `destroy` → assert invoice removed from DB.
    - All unauthenticated requests → assert 302 redirect to login.

- [ ] **T4.4 — Frontend: invoice panel on Campaign Show**
  - Add an "Invoices" tab/section to Campaign Show (alongside the KOL section from T1.5).
  - Table per invoice: influencer name, KOL platform, amount, status badge, paid date, actions.
  - Actions: generate invoice (opens form modal with pre-filled amount), change status, upload proof, delete.
  - **DoD**: Full invoice lifecycle (create → pending → paid with proof) manageable from the campaign page.
  - **Test**: Assert `campaign.show` Inertia response includes `invoices` prop (extend `CampaignShowTest.php`). No Vue test.

- [ ] **T4.5 — PDF export**
  - Install `barryvdh/laravel-dompdf` via Composer.
  - Route: `GET /campaign/{campaign}/invoice/{invoice}/pdf` — renders `resources/views/pdf/invoice.blade.php` and streams as PDF download.
  - PDF content: campaign name, influencer name, KOL platform + username, deliverable, amount, status, dates, optional notes.
  - **DoD**: PDF downloads with correct filename (`invoice-{id}.pdf`). Renders cleanly in a PDF viewer.
  - **Test**: `tests/Feature/Invoice/InvoicePdfTest.php`
    - GET the PDF route as authenticated user → assert response status 200 and `Content-Type: application/pdf`.
    - Assert `Content-Disposition` header contains `invoice-{id}.pdf`.
    - Assert unauthenticated request redirects to login.

---

## Section 5 — Dashboard Analytics
> Implements F8. Depends on Sections 2 and 4 for full accuracy, but can be built in parallel using whatever data exists.

- [ ] **T5.1 — Backend: analytics props for Dashboard**
  - Update `routes/web.php` dashboard route (or a dedicated `DashboardController`) to pass Inertia props:
    - `totalInfluencers` — count of all influencers
    - `totalCampaigns` — count of all campaigns
    - `activeCampaigns` — count where `status = ongoing`
    - `totalInvoiced` — sum of `invoices.amount`
    - `totalPaid` — sum of `invoices.amount` where `status = paid`
    - `topInfluencers` — top 5 influencers ordered by average `engagement_rate` across their KOLs
    - `campaignStatusBreakdown` — count grouped by `status`
  - **DoD**: `Dashboard` Vue component receives all props. Verified by inspecting Inertia page data in browser DevTools.
  - **Test**: `tests/Feature/DashboardTest.php` (extend existing file)
    - Seed known data (3 influencers, 2 campaigns with status `ongoing`, 1 invoice paid for 100000).
    - GET `/dashboard` as auth user → assert Inertia props: `totalInfluencers = 3`, `activeCampaigns = 2`, `totalPaid = 100000`.
    - Assert `topInfluencers` contains at most 5 entries and is sorted descending by engagement rate.
    - Assert `campaignStatusBreakdown` is an array with status keys matching `CampaignStatus` enum values.

- [ ] **T5.2 — Frontend: stat cards**
  - Replace the placeholder in `resources/js/pages/Dashboard.vue` with 5 stat cards: Total Influencers, Total Campaigns, Active Campaigns, Total Invoiced (Rp), Total Paid (Rp).
  - Use existing `Card` components from `components/ui/card/`.
  - **DoD**: Cards display correct values from Inertia props. Currency formatted (IDR or locale-appropriate).
  - **Test**: No backend test. Covered by T5.1 feature test (props are correct).

- [ ] **T5.3 — Frontend: top influencers table**
  - Table listing top 5 influencers from `topInfluencers` prop: name, niche tags, avg engagement rate, platform badges.
  - Row is clickable, navigates to `influencer.show`.
  - **DoD**: Table renders; navigation works; empty state shown if no influencers exist.
  - **Test**: No backend test. Covered by T5.1 feature test (props contain `topInfluencers` with correct shape).

- [ ] **T5.4 — Frontend: campaign status chart**
  - Doughnut chart using PrimeVue `Chart` component (wraps Chart.js) fed from `campaignStatusBreakdown` prop.
  - Labels: Draft, Ongoing, Completed, Cancelled — with distinct colors matching status badge colors used elsewhere in the app.
  - **DoD**: Chart renders with correct data. Shows "No data" empty state if no campaigns exist.
  - **Test**: No backend test. Covered by T5.1 (prop `campaignStatusBreakdown` is correct shape).

---

## Section 6 — CreatorDB Integration UI
> Completes F9. Service layer (`CreatorDBServiceV1`) is already implemented. Independent of Sections 1–5.

- [ ] **T6.1 — Backend: CreatorDB search endpoint**
  - `App\Http\Controllers\Web\CreatorDB\CreatorDBController::search(Request $request)`
  - Validates `platform` (must be in Platform enum) and `username` (required string).
  - Calls the appropriate `CreatorDBServiceInterface` method based on platform, returns normalized JSON.
  - Route: `GET /creator-db/search`.
  - **DoD**: Endpoint returns profile metrics for a valid username. Returns `422` on missing/invalid params. Returns `502` and logs error if CreatorDB API fails.
  - **Test**: `tests/Feature/CreatorDB/CreatorDBControllerTest.php`
    - Bind a mock `CreatorDBServiceInterface` in the container returning a fixed response.
    - GET with valid platform + username → assert 200 and JSON contains `followers`, `engagement_rate`.
    - GET with missing `username` → assert 422.
    - GET with invalid `platform` → assert 422.
    - Mock service throwing exception → assert 502.

- [ ] **T6.2 — Backend: import KOL from CreatorDB**
  - `CreatorDBController::import(Request $request, Influencer $influencer)`
  - Accepts `{ platform, username }`, fetches from CreatorDB, maps response to `key_opinion_leaders` columns, creates KOL record, sets `synced_at = now()`.
  - Route: `POST /influencer/{influencer}/kol/import`.
  - **DoD**: KOL created with metrics pre-filled from CreatorDB. `synced_at` set. Returns redirect to influencer show or JSON for Inertia.
  - **Test**: `tests/Feature/CreatorDB/CreatorDBImportTest.php`
    - Mock service returning fixture data. POST → assert KOL record created in DB with correct `followers`, `platform`, `synced_at` not null.
    - Mock service throwing exception → assert no KOL created, error response returned.

- [ ] **T6.3 — Backend: manual sync job for existing KOL**
  - `App\Jobs\SyncKolFromCreatorDB` — sets `syncing_at = now()` on the KOL, calls CreatorDB for fresh metrics, updates all metric columns, sets `synced_at = now()`, clears `syncing_at`.
  - On any exception: clears `syncing_at`, logs the error.
  - Route: `POST /influencer/{influencer}/kol/{kol}/sync/creator-db` → dispatches job, returns immediately.
  - **DoD**: Job runs on the queue. KOL metrics update. `syncing_at` is null again after completion. Error does not leave `syncing_at` stuck.
  - **Test**: `tests/Unit/Jobs/SyncKolFromCreatorDBTest.php`
    - Mock service returning fixture. Call `handle()` directly → assert KOL `followers` updated, `synced_at` set, `syncing_at` null.
    - Mock service throwing exception → assert `syncing_at` is null after handle (error path clears it), error logged.
  - **Test (HTTP)**: `tests/Feature/CreatorDB/CreatorDBControllerTest.php` — POST to sync route → assert `Queue::assertPushed(SyncKolFromCreatorDB::class)`.

- [ ] **T6.4 — Frontend: import modal on Influencer Create/Edit**
  - "Import from CreatorDB" button on the KOL section of `influencer/Create.vue` and `influencer/Edit.vue`.
  - Opens a modal: platform dropdown + username text input + "Search" button.
  - Search calls T6.1 endpoint and displays a preview card (followers, engagement rate, link).
  - "Add this KOL" button calls T6.2 import endpoint; on success, KOL appears in the list.
  - **DoD**: User can find and import a KOL without manually typing all metrics. Loading and error states handled.
  - **Test**: No backend test. Covered by T6.1 and T6.2 feature tests.

- [ ] **T6.5 — Frontend: sync button on Influencer Show**
  - Per-KOL row on `influencer/Show.vue`: "Sync" dropdown with option "Sync via CreatorDB".
  - Button disabled (with spinner) while `syncing_at` is not null (prop passed from controller).
  - On click: calls T6.3 endpoint; page reloads after a short delay to show updated metrics.
  - **DoD**: Sync triggers correctly. Button reflects syncing state. Metrics visibly update after sync.
  - **Test**: Assert `influencer.show` Inertia response includes `syncing_at` on each KOL (extend influencer show test). No Vue test.

---

## Section 7 — Apify Scraper Integration
> Implements F10. Extends the sync flow from Section 6. Independent prerequisite: Section 6 sync pattern (T6.3, T6.5).

- [ ] **T7.1 — Config & environment setup**
  - Add `APIFY_TOKEN=` to `.env.example`.
  - Add to `config/influenser.php`:
    ```php
    'apify' => [
        'token' => env('APIFY_TOKEN'),
        'actors' => [
            'tiktok'    => env('APIFY_ACTOR_TIKTOK'),
            'instagram' => env('APIFY_ACTOR_INSTAGRAM'),
            'youtube'   => env('APIFY_ACTOR_YOUTUBE'),
            'facebook'  => env('APIFY_ACTOR_FACEBOOK'),
        ],
    ],
    ```
  - **DoD**: Config keys exist and readable via `config('influenser.apify.token')`. `.env.example` updated.
  - **Test**: `tests/Unit/Config/ApifyConfigTest.php`
    - Assert `config('influenser.apify.token')` reads from `APIFY_TOKEN` env.
    - Assert all four actor keys exist in `config('influenser.apify.actors')`.

- [ ] **T7.2 — Apify service**
  - `App\Services\ThirdParty\ApifyServiceInterface` with method `runActor(string $actorId, array $input): array`.
  - `App\Services\ThirdParty\ApifyServiceV1` implementation: POST to `https://api.apify.com/v2/acts/{actorId}/run-sync-get-dataset-items` with bearer token, returns parsed dataset items array.
  - Register as singleton in `ThirdPartyServiceProvider` alongside CreatorDB binding.
  - **DoD**: Service resolves from container. `runActor` returns dataset array for a valid actor. Throws exception on non-2xx response.
  - **Test**: `tests/Unit/Services/ApifyServiceV1Test.php`
    - Fake `Http` facade returning a 200 JSON array. Call `runActor()` → assert returns the parsed array and HTTP call used Bearer token header.
    - Fake `Http` returning 500 → assert exception thrown.

- [ ] **T7.3 — Per-platform field mapping**
  - `App\Services\ThirdParty\ApifyKolSyncService` — injected with `ApifyServiceInterface`.
  - Method `sync(KeyOpinionLeader $kol): array` — resolves actor ID from `config('influenser.apify.actors.{platform}')`, builds actor input from `$kol->username`, calls `ApifyServiceInterface::runActor`, maps response fields to KOL column names.
  - Returns normalized array compatible with `KeyOpinionLeader::$fillable`.
  - **DoD**: Given a KOL with a supported platform, `sync()` returns an array with `followers`, `engagement_rate`, `views`, etc. Throws `\RuntimeException` for unsupported platform.
  - **Test**: `tests/Unit/Services/ApifyKolSyncServiceTest.php`
    - Mock `ApifyServiceInterface`. For each supported platform (tiktok, instagram, youtube, facebook): call `sync()` with a KOL of that platform → assert returned array contains `followers` key.
    - Pass a KOL with platform `linkedin` (unsupported) → assert `\RuntimeException` thrown.

- [ ] **T7.4 — Queued sync job: `SyncKolFromApify`**
  - `App\Jobs\SyncKolFromApify`: sets `syncing_at`, calls `ApifyKolSyncService::sync`, writes returned metrics to KOL, sets `synced_at`, clears `syncing_at`.
  - On failure: clears `syncing_at`, logs error. If `CREATOR_DB_API` is configured, falls back by dispatching `SyncKolFromCreatorDB` (T6.3).
  - Route: `POST /influencer/{influencer}/kol/{kol}/sync/apify` → dispatches job.
  - **DoD**: Job updates KOL metrics. Fallback triggers if Apify fails and CreatorDB is configured. `syncing_at` always cleared regardless of outcome.
  - **Test**: `tests/Unit/Jobs/SyncKolFromApifyTest.php`
    - Mock `ApifyKolSyncService` returning fixture. Call `handle()` → assert KOL metrics updated, `synced_at` set, `syncing_at` null.
    - Mock service throwing exception, `CREATOR_DB_API` set → assert `SyncKolFromCreatorDB` dispatched, `syncing_at` null.
    - Mock service throwing exception, `CREATOR_DB_API` not set → assert no fallback job dispatched, `syncing_at` null.

- [ ] **T7.5 — Frontend: sync source selector**
  - Extend the "Sync" button from T6.5 into a `DropdownMenu` with two items: "Sync via CreatorDB" and "Sync via Apify".
  - Each option dispatches to its respective endpoint (T6.3 or T7.4). Disabled while `syncing_at` is set.
  - Hide "Sync via Apify" option if platform is not in the supported actor list (TikTok, Instagram, YouTube, Facebook).
  - **DoD**: Both sync sources selectable per KOL. Unsupported platforms show only the CreatorDB option. Syncing state reflected correctly for both.
  - **Test**: No backend test. Covered by T6.3 and T7.4 job tests.
