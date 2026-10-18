# 0001. Member plan, author price change, and metered access

**Date**: 2026-10-17
**Status**: In Progress

## Summary

This adds a $15 a month Member plan for reading and listening and raises the Author plan to $25 for new subscribers. People who already pay $20 keep that price. Free users get 5 articles and 2 sermons a month on the website, then see a preview with a subscribe box. Sermon audio moves to links that expire after a few hours, so the limit can't be dodged by copying a link. One rule, `User::hasFullAccess()`, decides who gets everything, and the mobile API (web 20) will use the same rule.

## Requirements

**User stories**:
- As a reader, I want a few free articles and sermons each month so I can try Baptist Cloud before I pay.
- As a reader, I want a $15 Member plan so I can read and listen without limits, on the website and in the app.
- As an author, I want the $25 Author plan to include everything Member has plus my own ads, so I only need one subscription.
- As a current $20 author, I want to keep my price and my features with no action on my part.
- As the site owner, I want one clear rule for who has full access, so the website and the mobile API agree.

**Acceptance criteria**:
- **AC-1**: A signed in user without a subscription who opens `/billing` sees two plans: **Member, $15/month** (unlimited articles and sermons, plus the mobile app) and **Author, $25/month** (everything in Member, plus your own ad on your articles). Each plan has its own Subscribe button.
- **AC-2**: Subscribing to Member opens Stripe Checkout with the `STRIPE_MEMBER_PRICE` price. Subscribing to Author opens it with the `STRIPE_AUTHOR_PRICE` price. Any other `plan` value fails validation and starts no checkout. A user who already has a subscription is sent to `/billing` (the portal) and never gets a second subscription.
- **AC-3**: A subscriber who opens `/billing` is sent to the Stripe billing portal. From there they can switch between Member and Author and cancel.
- **AC-4**: A user on a legacy $20 author price (listed in `STRIPE_AUTHOR_LEGACY_PRICES`) keeps that price. Their subscription is never changed by this feature, and they count as an Author for every check. A valid subscription on a price that isn't the Member price also counts as Author (safe legacy default) and logs a warning naming the unknown price.
- **AC-5**: `User::hasFullAccess()` returns true for admins, users with the `is_author` flag, and users whose `default` subscription is on the Member price, the Author price, or a legacy author price, as long as that subscription is active, past due while Stripe retries the card, or canceled but still inside the paid period. In every other case it returns false.
- **AC-6**: Author ads (`User::canRunAds()` and the ad fields in the post form) are available only to admins, `is_author` users, and users on the Author or a legacy author price. The Member plan does **not** unlock author ads.
- **AC-7**: When a free user opens a published article, they see the full article if they already opened it this month or have opened fewer than 5 different articles this month. Opening a new one records a view. Otherwise they see the paywall.
- **AC-8**: When a free user opens a sermon page, the same rule applies with a limit of 2 sermons a month. The article and sermon counters are separate.
- **AC-9**: The article paywall shows the title, the author, the featured image, and a short preview of the article body, followed by a subscribe box. The preview is the first 3 blocks, cut to at most 120 words, and never the whole post: when those blocks would be the entire article, the preview is its first half instead. A short post still shows the paywall. The sermon paywall shows the title, the author, the cover image (the existing `x-sermon-default` component, with its fallback), and the description, followed by a subscribe box. The site ads stay. The subscribe box names the Member plan and its $15 price, links to `/billing`, and says when the free count resets ("Your free articles reset on November 1").
- **AC-10**: Users with full access, and owners opening their own article or sermon, never record a view and never see the paywall.
- **AC-11**: When a free user opens an item (new or reopened) and they have 1 or 0 free views of that kind left this month, a small note appears with exactly one of: "1 free article left this month" · "That was your last free article this month" · "1 free sermon left this month" · "That was your last free sermon this month". Otherwise no counter shows.
- **AC-12**: The sermon page plays audio from a temporary signed link on the `sermons` disk that expires 6 hours after the page loads. If `audio_url` is empty, no player shows. The `/sermons/feed` list has no inline audio player and records no views. Each item links to its sermon page instead.
- **AC-13**: A paywalled page's HTML contains neither the full article body nor any audio link. No other page (home, category, public profile, `/sermons`, search, sermon cards) renders full article bodies or audio links. This was checked against the current views and must stay true.
- **AC-14**: View records older than 3 months are deleted by a daily scheduled job.
- **AC-15**: The admin users table labels each user Admin, Author, Member, or Free, using the same rules as AC-5 and AC-6.
- **AC-16**: Draft rules stay as they are: a non owner who isn't an admin gets a 404 for an unpublished article. No view is recorded for drafts.

## Decision

**Chosen option**: Option 1: One Cashier subscription, the tier read from its Stripe price, plus a per user monthly view meter.

Keep the single `default` Cashier subscription and work out the tier from its Stripe price ID. Count free views in a new `content_views` table, one row per user, item, and month. Serve sermon audio through signed links that expire.

## Rationale

Reasoning and options: see [rationale.md](rationale.md).

## Feature design

**Data model sketch**:

| Table | Column | Type | Rules |
|---|---|---|---|
| `content_views` (new) | `id` | bigint PK | |
| | `user_id` | FK users.id | required, `cascadeOnDelete` |
| | `kind` | string(16) | `post` or `sermon` (plain string, no morph map; a `media` table already uses morphs, so an enforced morph map is avoided) |
| | `item_id` | unsigned bigint | id of the post or sermon (no FK) |
| | `period` | date | first day of the month in `config('app.timezone')` |
| | `created_at` | timestamp | no `updated_at` |
| | unique | (`user_id`, `kind`, `item_id`, `period`) | reopening the same item in a month is free |
| | index | (`user_id`, `period`, `kind`) | count used views fast |
| `subscriptions` (Cashier, unchanged) | `stripe_price` | string | the tier comes from this |

`User` hasMany `ContentView` (1:N). No relation from a view back to its item (nothing reads it). Rows for deleted posts or sermons stay and still count toward that month. `ContentView` uses Laravel's `Prunable` trait with `prunable()` returning `where('period', '<', now()->subMonthsNoOverflow(3)->startOfMonth())`.

**Plan rule** (on `User`, one place, reused by the web 20 API):
- `planTier()`: computed once per request and cached on the model instance. Returns `admin`, `author`, `member`, or `free`:
  - `is_admin` → `admin`
  - `is_author` → `author`
  - `subscription('default')` is missing or not `valid()` → `free`
  - its `stripe_price` equals `member_price` → `member`
  - its price is `author_price` or in `author_legacy_prices` → `author`
  - any other valid price → `author`, plus `Log::warning` naming the price (AC-4)
- Config values are trimmed and empty strings dropped (`array_filter`) for all three keys, so an unset env never matches.
- `hasFullAccess()`: `planTier() !== 'free'`
- `canRunAds()`: `in_array(planTier(), ['admin', 'author'])` (replaces the old `subscribed()` check)
- `planLabel()`: `ucfirst(planTier())`
- Call `Cashier::keepPastDueSubscriptionsActive()` in `AppServiceProvider::register()`, so a past due subscription still counts as valid (AC-5). `incomplete` and `unpaid` stay invalid, so an unfinished Checkout gives no access.
- `BillingController` keeps using `subscribed()` to choose between the portal and the plans page. A canceled user still in their paid period goes to the portal, where they can resume.

**Meter** (`App\Support\ContentMeter`, new):
- `limits`: `post` 5, `sermon` 2 (class constants)
- `check(User $user, Post|Sermon $item): MeterResult`, which returns `allowed` (bool), `remaining` (int or null: the limit minus rows used this period, after any insert), `resetsOn` (date: the first day of next month)
- Order: full access or owner → allowed, nothing recorded, `remaining` null. Otherwise: is there already a row for this period → allowed, nothing new, `remaining` computed from the current count. Otherwise: count rows for (`user`, `period`, `kind`). If that's under the limit, `insertOrIgnore` a row → allowed, `remaining` = limit minus (count + 1). If not → not allowed, `remaining` 0.
- Controllers run their existing checks first (the draft 404 in `PostController@show`), then the meter.
- Article paywall path: split the **raw** Markdown with `preg_split('/\R{2,}/', trim($post->content))`, take the first 3 blocks, cap them at 120 words with `Str::words`, and if that is still every word of the post, use the first half of its words instead (a 1 word post gets an empty preview). Render only that preview. The full rendered body is never assigned to the view on this path. A blank line inside a code block or list may cut a block early, which is accepted.

**API surface** (web routes; Blade pages, not JSON):

| Route | Method | Key inputs | Key outputs | Auth | Key errors |
|---|---|---|---|---|---|
| `/billing` | GET | none | free user: plans view (two cards). subscriber: redirect to Stripe portal | auth, verified | portal API error: redirect home with error (existing) |
| `/billing/subscribe` | POST | `plan`: `required\|in:member,author` (via `$request->validate`, a new `Request` param) | redirect to Stripe Checkout for the matching price | auth, verified | bad plan: redirect back with validation errors (form post, not JSON). Price env missing or Stripe error: back to `/billing` with error. Already subscribed: redirect `/billing` |
| `/billing/success` | GET | none | redirect home: "Thanks for subscribing! Your plan will be ready in a moment." (the webhook can take a few seconds, so the user may briefly still be Free) | auth, verified | none |
| `/@{username}/{post:slug}` | GET | route params | full article, or the paywall view, plus a near limit note | auth, verified | 404 draft for a non owner (unchanged) |
| `/sermons/{sermon}` | GET | route param | full sermon with a signed audio link, or the paywall view, plus a near limit note | auth, verified | none |
| `/sermons/feed` | GET | none | list without inline players, a Listen link per item | auth, verified | none |

**Value sourcing**:

| Action | Value produced / displayed | Source |
|---|---|---|
| subscribe | Stripe price for `member` | `config('services.stripe.member_price')` ← `STRIPE_MEMBER_PRICE` |
| subscribe | Stripe price for `author` | `config('services.stripe.author_price')` ← `STRIPE_AUTHOR_PRICE` (the new $25 price) |
| plan rule | legacy author prices | `config('services.stripe.author_legacy_prices')` ← `STRIPE_AUTHOR_LEGACY_PRICES`, comma separated, exploded and empty values filtered |
| plan rule | user's current price | `subscription('default')->stripe_price`, read once in `planTier()` |
| billing view | "$15/month", "$25/month" display text | `resources/views/components/plan-cards.blade.php` (both plans) |
| paywall box | Member name, "$15/month", reset date | `resources/views/components/subscribe-box.blade.php` (Member only; takes `kind` and `resetsOn`) |
| meter | current period | `now(config('app.timezone'))->startOfMonth()->toDateString()` |
| meter | free limit per type | `ContentMeter` constants (`post` 5, `sermon` 2) |
| meter | views used | count of `content_views` for (`user_id`, `period`, `kind`) |
| meter | `resetsOn` shown in the paywall and note | first day of next month, same timezone (UTC today), formatted `F j` |
| meter | owner check | `post.user_id` / `sermon.user_id` equals `auth()->id()` |
| article paywall | preview text | the post's Markdown split on blank lines, first 3 blocks, at most 120 words, never the whole post (then its first half), rendered with `App\Support\Markdown::render` |
| sermon page | signed audio link | `Storage::disk('sermons')->temporaryUrl(Sermon::audioPath(), now()->addHours(6))` |
| sermon page | storage path of the audio | `Sermon::audioPath()`: `'sermons/'.basename(parse_url($this->audio_url, PHP_URL_PATH))`, matching `SermonController::deleteStored`. Null when `audio_url` is empty |
| admin table | plan label | `User::planLabel()`, with `subscriptions` eager loaded in the admin users query to avoid one query per row |

**Key invariants**:
- One `default` subscription per user. `subscribe` refuses when `subscribed()` is true.
- A free user never has more than 5 article rows or 2 sermon rows in one period, except for the rare race below.
- `canRunAds()` is never true because of the Member price.
- The audio link never appears in HTML for a user who isn't allowed to listen.
- Thumbnails and post images stay on the public `postImages` bucket. Only the `sermons` bucket goes private.

**Security model**:
- All metered pages already require `auth` and `verified`. Views are counted per account, not per cookie.
- `plan` is checked against an allowlist. The price ID always comes from config, never from the request.
- Signed audio links expire after 6 hours. Someone could share a link for that long, which is acceptable for this product.
- Stripe webhooks keep flowing through Cashier's existing `/stripe/webhook` route, which is verified with `STRIPE_WEBHOOK_SECRET`.
- Payment data stays on Stripe (Checkout and portal). This feature adds no card data handling.

**Configuration required**:
- `STRIPE_MEMBER_PRICE`: the new $15/month Member price ID.
- `STRIPE_AUTHOR_PRICE`: change it to the new $25/month Author price ID.
- `STRIPE_AUTHOR_LEGACY_PRICES`: the old $20 author price ID (comma separated if there's more than one).
- `config/filesystems.php`: add `'use_path_style_endpoint' => true` to the `sermons` disk, so signed links use the `endpoint/bucket/key` form IONOS expects (the same form the stored URLs already use).
- Stripe dashboard (manual): add both prices; in the billing portal settings, allow switching between the Member and the $25 Author prices only (not the legacy one); keep Smart Retries on.
- IONOS (manual, last step): make the `sermons-bc` bucket private after the signed links are live.
- Production host must run Laravel's scheduler (`php artisan schedule:run` every minute) for the daily prune.

**Critical test scenarios**:
- Happy path: a free user opens 5 different articles (all full), the 6th shows the paywall, reopening article 1 shows it in full. Verifies **AC-7**, **AC-13**.
- Sermons: a free user opens 2 sermons (full, with an audio player), the 3rd shows the paywall with no `<audio>` tag. The article count is untouched. Verifies **AC-8**, **AC-13**.
- Full access: a Member, an Author, an admin, and an `is_author` user each open 10 articles, with no rows recorded and no paywall. Verifies **AC-5**, **AC-10**.
- Owner: a free author opens their own post 10 times, with no rows. Verifies **AC-10**.
- Ads: a Member price subscriber can't see the post form ad fields, and `canRunAds()` is false. A legacy price subscriber can. Verifies **AC-4**, **AC-6**.
- Past due: a subscription with status `past_due` still has full access. One with status `canceled` and past `ends_at` does not. Verifies **AC-5**.
- Subscribe: `plan=member` uses the member price, `plan=gold` gives `assertSessionHasErrors('plan')`, and an already subscribed user is redirected. Verifies **AC-2**.
- Unknown price: a valid subscription on `price_unknown` counts as Author and logs a warning. Verifies **AC-4**.
- Near limit: the 4th article shows "1 free article left", the 5th shows "last free article", and the 1st sermon shows "1 free sermon left". Verifies **AC-11**.
- Prune: rows with a period 4 months back are deleted, last month's stay. Verifies **AC-14**.
- Feed: the `/sermons/feed` HTML has no `<audio>` tag. Verifies **AC-12**.
- Stripe and S3 are faked. Subscriptions are created as rows, with the `services.stripe.*` config set in each test. Existing fixtures using `price_author_monthly` either set it as a legacy price or rely on the unknown price fallback. `Storage::fake('sermons')` can't create temporary links, so tests call `Storage::disk('sermons')->buildTemporaryUrlsUsing(fn ($path) => 'https://signed.test/'.$path)`. No live calls, per the coding standards.
- Existing tests to update: `BillingTest` (the $20 copy and the author only subscribe flow) and `PostAdsTest` (the subscription fixture price).

## Build plan

Tracer Bullet: the plan rule first, because everything depends on it. Then articles end to end (meter, paywall, billing). Then sermons thicken the same path. The bucket switch comes last.

1. [x] Add the config keys (`member_price`, `author_legacy_prices`) to `config/services.php` and `.env.example`. Add `planTier`, `hasFullAccess`, and `planLabel` to `User`. Point `canRunAds` at the tier. Replace the inline check in `livewire/post-form.blade.php` with `canRunAds()`. Use `planLabel()` in `users-table` and eager load `subscriptions` in the admin query. Call `keepPastDueSubscriptionsActive()`. Satisfies **AC-4**, **AC-5**, **AC-6**, **AC-15**
2. [x] Create the `content_views` migration and the `ContentView` model (`kind` string, `Prunable` with the 3 month cutoff). Add `Schedule::command('model:prune')->daily()` to `routes/console.php`. Satisfies **AC-7**, **AC-8**, **AC-14**
3. [x] Build `App\Support\ContentMeter` and `MeterResult`. Satisfies **AC-7**, **AC-8**, **AC-10**
4. [x] Meter `PostController@show` after the draft 404 check: render the full article or a paywall view (raw Markdown preview plus the `subscribe-box` component) and the near limit note. Satisfies **AC-7**, **AC-9**, **AC-11**, **AC-13**, **AC-16**
5. [x] Build the `plan-cards` component and the two plan `/billing` page. Add `plan` validation and the price mapping in `BillingController@subscribe`, plus the new success copy. Satisfies **AC-1**, **AC-2**, **AC-3**
6. [x] Meter `SermonController@show`. Add `Sermon::audioPath()`, the path style setting on the `sermons` disk, and the signed link in `sermons/single.blade.php` (hidden when there's no audio). Add the sermon paywall. Remove the inline player from `sermons/index.blade.php` in favor of a Listen link. Satisfies **AC-8**, **AC-9**, **AC-11**, **AC-12**, **AC-13**
7. [x] Write Pest feature tests for the scenarios above (`tests/Feature/MeteredAccessTest.php`) and update `BillingTest.php` and `PostAdsTest.php`. Satisfies **AC-1** to **AC-16**
8. [x] Rollout (manual, see the Migration plan): create the Stripe prices and set the env, deploy, check playback, then make the bucket private. Satisfies **AC-4**, **AC-12**

## Migration plan

**Strategy**: no data migration. A phased rollout, because one manual step can break audio.
**Phases**:
1. In Stripe, create the $15 Member price and a new $25 price on the Author product. Set `STRIPE_MEMBER_PRICE`, `STRIPE_AUTHOR_PRICE` (new), and `STRIPE_AUTHOR_LEGACY_PRICES` (old $20). Configure plan switching in the portal.
2. Deploy. Current $20 subscribers are matched through the legacy list, so nothing changes for them.
3. Check that a sermon plays from its signed link in production. Then set the `sermons-bc` bucket to private in IONOS.
**Rollback**: revert the deploy and set the bucket back to public. Member subscriptions made in the meantime stay valid in Stripe. Refund or switch them by hand if you roll back for good.
**Risks**: making the bucket private before phase 3 breaks every sermon. Forgetting the legacy price ID would strip author ads from current $20 subscribers. Checking AC-4 and AC-6 after the deploy catches it.

## Consequences

**Positive**:
- One access rule for the website and the coming mobile API.
- No new services or packages: Cashier, Stripe portal, Laravel `Prunable`, and S3 temporary links are all already available.
- Current authors see no change.

**Negative / tradeoffs**:
- Articles that are free today become limited for regular readers, so some readers and some search traffic will drop off. Authors may notice lower reach.
- A race between two tabs can let a free user get one extra view (count, then insert). Accepted rather than adding locks.
- Signed links can be shared for 6 hours.
- Making the bucket private means any outside place that embedded a sermon's plain link stops working.
- The meter resets on UTC month boundaries (`app.timezone` is UTC), which is evening of the last day for US readers.

**Neutral**:
- Each page load of a sermon creates a new signed link (a cheap local signature, no network call).
- `content_views` grows by at most 7 rows per free user per month and is pruned after 3 months.

## Follow-up

- [ ] Scope: web 19 now covers reading on the website. Move "Listener plan for reading" out of the web Deferred list and rename feature 19 to "Member plan".
- [ ] web 20 (mobile API) should gate on `User::hasFullAccess()`. The app has no free views. web 21 should use the same `Sermon::audioPath()` and signed link.
- [ ] Confirm the production host runs `schedule:run`. No scheduled tasks exist yet.
- [ ] Optional later: a "Your plan" line on the profile page, and annual pricing.
- [ ] Existing bug, out of scope: the sermon page's report link points at `/report-post/{sermon->id}`, but posts and sermons share no id space. Worth a `/debug` fix on its own.
