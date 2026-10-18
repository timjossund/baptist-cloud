# Scope: Baptist Cloud web

The Laravel website where Baptist pastors and writers publish articles and sermons, readers follow authors, and churches post open positions. This slice adds a lower priced listener plan and a mobile API so the `audio-app` can play sermons for subscribers.

**Build approach:** Tracer Bullet (prove one real path end to end, website billing through API to phone, then thicken it one strand at a time).
**Workflow:** Beta (after `/develop`: `/check verify`, then `/test`). The project default level of rigor. `/architect` is the recommended first stop for a feature with a real decision, but skippable when you already know the build. Any feature can carry its own tag (e.g. `· GA`) to do more or less.

_These are recommendations to keep your build orderly, not requirements. Skip anything that does not fit: if you already know how to build a feature, use `/develop` and skip `/architect`. You decide when a feature is `done`._

_The shipped features below are also tracked in `blueprint/build-plan.md` (same numbers 1 to 17). That plan stays the record for the Blueprint workflow; this scope adds the mobile work on top._

## At a glance

| # | Feature | Phase | Status |
|---|---------|-------|--------|
| 1 | Accounts and auth | Shipped | existing |
| 2 | Profiles | Shipped | existing |
| 3 | Articles | Shipped | existing |
| 4 | Home feed and categories | Shipped | existing |
| 5 | Following | Shipped | existing |
| 6 | Likes | Shipped | existing |
| 7 | Post reporting | Shipped | existing |
| 8 | Sermons | Shipped | existing |
| 9 | Search | Shipped | existing |
| 10 | Position listings | Shipped | existing |
| 11 | Ads | Shipped | existing |
| 12 | Admin dashboard | Shipped | existing |
| 13 | Subscriptions (Spark) | Shipped | existing |
| 14 | Queued image processing | Shipped | existing |
| 15 | Turnstile on login and registration | Shipped | existing |
| 16 | Position approval | Shipped | existing |
| 17 | Author plan on Cashier | Shipped | existing |
| 18 | Markdown sandbox | Shipped | existing |
| 19 | Member plan | Foundation | done |
| 20 | Mobile API and token sign in | Foundation | done |
| 21 | Sermon API | Slice 1 | planned |
| 22 | Search and series API | Slice 3 | planned |
| 23 | Following API | Slice 4 | planned |

## Shipped

### 1. Accounts and auth · existing
Registration, login, email verification, password reset (Breeze). code in `routes/auth.php`, `app/Http/Controllers/Auth/`

### 2. Profiles · existing
Profile editing with avatar upload, public author pages at `/@username`. code in `app/Http/Controllers/ProfileController.php`, `PublicProfileController.php`

### 3. Articles · existing
Markdown posts with featured image, category, tags, drafts, and publishing. code in `app/Http/Controllers/PostController.php`, `app/Livewire/PostForm.php`

### 4. Home feed and categories · existing
Published post feed, followed authors when signed in, category browsing. code in `app/Http/Controllers/PostController.php`

### 5. Following · existing
Follow and unfollow authors, follower list. code in `app/Http/Controllers/FollowerController.php`

### 6. Likes · existing
Rate limited post likes. code in `app/Http/Controllers/LikeController.php`

### 7. Post reporting · existing
Users report posts, admins review and clear reports. code in `app/Http/Controllers/ReportingController.php`

### 8. Sermons · existing
MP3 sermons with cover image, series, tags, and feed, stored on IONOS object storage. code in `app/Http/Controllers/SermonController.php`, `app/Models/Sermon.php`

### 9. Search · existing
Live Livewire search for posts, authors, and sermons. code in `app/Livewire/Search.php`, `SearchAudio.php`

### 10. Position listings · existing
Church position board with create and edit. code in `app/Http/Controllers/ListingController.php`

### 11. Ads · existing
Admin managed site ads and per post author ads. code in `app/Http/Controllers/AdController.php`

### 12. Admin dashboard · existing
Promote or revoke admins, delete users, manage ads. code in `app/Http/Controllers/AdminController.php`

### 13. Subscriptions (Spark) · existing
The original Spark billing, since replaced by feature 17. code in `blueprint/history/features/`

### 14. Queued image processing · existing
Post images resized on the queue, Horizon for admins. code in `app/Jobs/`

### 15. Turnstile on login and registration · existing
Cloudflare Turnstile restored on login and registration. code in `app/Rules/Turnstile.php`

### 16. Position approval · existing
Verified users submit positions, admins approve before they go live, posters edit their own. code in `app/Http/Controllers/AdminController.php`, `ListingController.php`

### 17. Author plan on Cashier · existing
$20 author subscription through Cashier (Stripe) that unlocks per article author ads. code in `app/Http/Controllers/BillingController.php`

### 18. Markdown sandbox · existing
A page where authors practice Markdown before writing. code in `app/Livewire/MarkdownSandbox.php`

## Foundations

### 19. Member plan · done · GA
A $15 monthly Member plan for reading and listening (website and mobile app), the Author plan raised to $25 for new subscribers (current $20 subscribers keep their price), and a metered website paywall: 5 free articles and 2 free sermons a month. People subscribe on the website only; the app just checks it.
**Done when:** a user can subscribe to Member or Author on `/billing`, switch or cancel in the billing portal, free users hit the paywall after their monthly free views, and one rule (`User::hasFullAccess()`) says who has full access that the API can check.
spec [0001](../../specs/web/0001-member-plan-metered-access/index.md) · code in `app/Support/ContentMeter.php`, `app/Models/User.php`, `app/Http/Controllers/BillingController.php`
- [x] Design it (spec): `/architect listener plan`
- [x] Build it: `/develop member plan`
  - [x] Plan rule: tiers from the Stripe price, author ads limited to Author, admin labels (AC-4, AC-5, AC-6, AC-15)
  - [x] Article meter and paywall: `content_views` table, `ContentMeter`, preview, near limit note, daily prune (AC-7, AC-9, AC-10, AC-11, AC-13, AC-14, AC-16)
  - [x] Two plan billing page and subscribe by plan (AC-1, AC-2, AC-3)
  - [x] Sermon meter, signed audio links, no players in the feed (AC-8, AC-12, AC-13)
  - [x] Rollout: Stripe prices and env, deploy, then make the sermons bucket private (AC-4, AC-12)
- [x] Verify it: `/check verify member plan`
- [x] Test it: `/test member plan`
- [x] Review it (fresh model): `/check review member plan`
- [x] Document it: `/document member plan`

### 20. Mobile API and token sign in · done · GA
Add the API layer to Laravel: versioned JSON routes, token sign in for the phone app, sign out that revokes the token, rate limits, and the listening access check from feature 19.
**Done when:** the app can trade an email and password for a token, call an authenticated route, and sign out; unverified users and users without listening access get a clear response the app can show; repeated failed sign ins are throttled.
spec [0002](../../specs/web/0002-mobile-api-token-sign-in/index.md) · code in `routes/api.php`, `app/Http/Controllers/Api/`, `app/Exceptions/ApiException.php`, `bootstrap/app.php`
- [x] Design it (spec): `/architect mobile API and token sign in`
- [x] Build it: `/develop mobile API and token sign in`
  - [x] Tracer thread: Sanctum installed, JSON error layer, sign in, `/me`, sign out (AC-1, AC-2, AC-3, AC-5, AC-6, AC-7, AC-10, AC-13, AC-14)
  - [x] Sign in throttles and the per user API limit (AC-4, AC-12)
  - [x] Token lifecycle: 90 day idle expiry and daily prune, revocation on password change, reset, and account deletion (AC-8, AC-9)
  - [x] `full.access` gate ready for feature 21, plus the Pest feature suite (AC-11, AC-1 to AC-14)
- [x] Verify it: `/check verify mobile API and token sign in`
- [x] Test it: `/test mobile API and token sign in`
- [x] Review it (fresh model): `/check review mobile API and token sign in`
- [x] Document it: `/document mobile API and token sign in`

## Slice 1: Sign in and play a sermon

### 21. Sermon API · needs a decision
The web half of slice 1 (pairs with audio-app 5): list sermons newest first and return one sermon with its details and a playable audio link.
**Done when:** a listener with access gets a paged sermon list and a sermon's detail with an audio link that streams on a phone; anyone without access is refused.
- [ ] Design it (spec): `/architect sermon API`

## Slice 3: Search and series

### 22. Search and series API
The web half of slice 3 (pairs with audio-app 7): search sermons and authors, list series and the sermons in each, following the patterns set in features 20 and 21.
**Done when:** search returns matching sermons and authors in pages, and a series lists its sermons in order.
- [ ] Build it: `/develop search and series API`

## Slice 4: Follow authors

### 23. Following API
The web half of slice 4 (pairs with audio-app 8): follow and unfollow authors from the app, list followed authors, and a feed of their sermons, reusing the site's existing follow data and rate limit.
**Done when:** following on the phone shows up on the website and the reverse; the followed feed lists only those authors' sermons.
- [ ] Build it: `/develop following API`

## Deferred
Out of scope for the current build pass, kept so the plan stays honest.
- **Author uploads from the app**: upload sermon audio from a phone · needs a decision

## Legend

**The decision box.** Every feature carries exactly one, the sub task whose label ends with `(spec)`. Skills locate it by that `(spec)` suffix, never by an exact label. Every other box is an execution box and `/architect` never ticks one.

**Feature lifecycle**: the scope updates as a feature moves; each row is what it shows and who sets it:

| State | Set by | The feature shows |
|---|---|---|
| `planned` · needs a decision | `/scope` | one box: `Design it (spec): /architect <feature>` |
| `in-progress` (designed) | **`/architect` at spec capture** | `Design it` ticked; spec linked; `Build it: /develop <feature>` + **2 to 5 milestones**; the tier's closing boxes (`Verify it` Alpha+, `Test it` Beta+, `Review it` + `Document it` GA) |
| `in-progress` (building) | `/develop` | milestone sub boxes tick one by one; code pointer filled |
| `in-progress` (verified) | `/check verify` | `Build it` + milestones ticked; `Verify it` ticked |
| `done` | **you, when you decide it is**; `/sync` reconciles | boxes you ran ticked, skipped ones marked skipped |

- **Next step** = the first unticked box (always a command or a tracked milestone).
- **needs a decision** = run `/architect` first; otherwise straight to `/develop`. The tag drops once the spec is captured.
- **Atomic build tasks live in the spec's `## Build plan`, not here**: the scope carries only the milestone rollup.
- **Status** `planned` → `in-progress` → `done`, plus `existing` (shipped before this workflow) and `dropped` (de-scoped, kept for history).
- **Workflow tier tag** beside a heading (e.g. `· GA`) sets that one feature's rigor above the project default; no tag inherits the default.
- **Workflow** (header line) is the project default, what runs after `/develop`: **Prototype** = nothing; **Alpha** = `/check verify`; **Beta** = `/check verify` then `/test`; **GA** = adds a fresh model `/check review` then `/document`.
- **Pointer line** (`spec <n> · code in <path>`): the spec link added by `/architect`, the code path by `/develop`.
