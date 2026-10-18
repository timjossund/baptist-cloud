# Verify: Member plan · spec 0001 · updated 2026-10-17
_Steps derived from spec 0001 acceptance criteria. `/check verify` runs these; `/test` locks the durable ones._

## UI / manual
- [x] As a free signed in user, open `/billing` → two cards: Member $15/month and Author $25/month, each with its own Subscribe button → AC-1
- [x] Click Subscribe on Member (Stripe test mode) → Checkout shows the `STRIPE_MEMBER_PRICE` price; repeat for Author → `STRIPE_AUTHOR_PRICE` → AC-2
- [x] As a subscriber, open `/billing` → sent to the Stripe portal, where you can switch Member and Author and cancel → AC-3
- [x] As a user on the legacy $20 price, open the post form → ad fields show; admin table says Author → AC-4, AC-6, AC-15
- [x] As a Member, open the post form → no ad fields; admin table says Member → AC-6, AC-15
- [x] As a free user, open 5 different articles → all full; the 4th shows "1 free article left this month", the 5th "That was your last free article this month" → AC-7, AC-11
- [x] Open a 6th article → title, author, featured image, a preview (first 3 blocks, at most 120 words, never the whole post), subscribe box naming Member, $15, and "Your free articles reset on <first of next month>"; site ads still show → AC-9
- [x] View source on the paywalled article → the 4th block and later are absent → AC-13
- [x] Open a paywalled article written as one long paragraph → only its first half (or 120 words) shows, the ending is absent from the source → AC-9, AC-13
- [x] Reopen article 1 → full again, no new view → AC-7
- [x] As a free user, open 2 sermons → each plays audio from a signed link (URL has `X-Amz-Signature`, expires in 6 hours) → AC-8, AC-12
- [x] Open a 3rd sermon → cover, title, author, description, subscribe box; no `<audio>` tag and no audio path in the source → AC-8, AC-9, AC-13
- [x] Open `/sermons/feed` → no inline players, a Listen link per sermon → AC-12
- [x] As an author, open your own article and sermon many times → always full, no note → AC-10
- [x] As another user, open someone's draft → 404 → AC-16
- [x] Check home, category, public profile, `/sermons`, and search → no full article bodies or audio links in the source → AC-13

## Value sourcing
- [ ] Set `STRIPE_MEMBER_PRICE` to a test price, subscribe as Member → the subscription row's `stripe_price` matches it → member price
- [x] Change `STRIPE_AUTHOR_PRICE`, subscribe as Author → Checkout uses the new value → author price
- [x] Put two IDs in `STRIPE_AUTHOR_LEGACY_PRICES` with a stray space and a trailing comma → both count as Author, an empty price does not match → legacy prices
- [x] Give a user a subscription on an unlisted price → planLabel is Author and the log has a warning naming the price → current price
- [ ] Use up free views near midnight UTC on the last day of a month → after UTC midnight the counter resets; the reset date shown is the 1st of the next month → period and resetsOn
- [x] Use 5 article views → the sermon count is still 2 free → limits and views used are per kind
- [x] A sermon whose `audio_url` is empty → no player; one with a full IONOS URL → the signed link points at `sermons/<file>.mp3` on the sermons bucket in path style → audioPath and signed link
- [x] Admin users page → one plan label per row with no extra query per row (check the debugbar or query log) → plan label

## Commands
- [x] `composer test` → all green, including `MeteredAccessTest` and `BillingTest` → AC-1 to AC-16
- [x] `php artisan db:table content_views` → unique (user_id, kind, item_id, period) and index (user_id, period, kind) → AC-7, AC-8
- [x] `php artisan schedule:list` → `model:prune` runs daily → AC-14
- [x] `php artisan model:prune --pretend` → only views older than 3 months would be removed → AC-14

## Acceptance-criteria coverage
- AC-1 billing cards · AC-2 checkout per plan · AC-3 portal · AC-4 legacy and unknown price · AC-5 full access tiers and statuses · AC-6 author ads · AC-7 article meter · AC-8 sermon meter · AC-9 paywall content · AC-10 full access and owners · AC-11 near limit note · AC-12 signed audio and feed · AC-13 no leaks · AC-14 prune · AC-15 admin labels · AC-16 drafts
