# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- A $15/month Member plan for unlimited articles and sermons. `/billing` now shows two plans, Member and Author, each with its own Subscribe button (see spec 0001).
- Monthly free limits for readers without a plan: 5 articles and 2 sermons a month, counted separately and reset on the 1st (UTC). After that, articles show a short preview (the first 3 blocks, at most 120 words, never the whole post) and sermons show their cover and description, each followed by a box that points to the Member plan.
- A small note when a free reader has 1 or 0 free views left this month ("1 free article left this month", "That was your last free sermon this month").
- `User::hasFullAccess()`, one rule for who gets everything: admins, `is_author` users, and Member, Author, or legacy Author subscribers. The coming mobile API is meant to use it too.
- A plan label (Admin, Author, Member, or Free) for each user in the admin users table.
- A `content_views` table that records free views, with old rows removed by a daily scheduled `model:prune`. Production needs `php artisan schedule:run` running every minute.
- New env settings: `STRIPE_MEMBER_PRICE` and `STRIPE_AUTHOR_LEGACY_PRICES` (comma separated).

### Changed
- The Author plan is now $25/month for new subscribers. Subscribers on a legacy $20 price listed in `STRIPE_AUTHOR_LEGACY_PRICES` keep their price and every Author feature. A subscription on an unknown price is treated as Author and logs a warning.
- Author ads are available only to admins, `is_author` users, and Author or legacy Author subscribers. The Member plan does not unlock them.
- Subscribing now requires a `plan` of `member` or `author`. Any other value fails validation, the price always comes from config, and existing subscribers are sent back to `/billing` (the Stripe portal) instead of getting a second subscription.
- Subscriptions that are past due while Stripe retries the card keep full access.
- Sermon audio now plays from a signed link that expires 6 hours after the page loads, and the `sermons` disk uses path style URLs.
- `/sermons/feed` no longer has inline audio players. Each sermon links to its own page instead.

### Security
- Paywalled pages never include the full article body or a sermon's audio link, and no listing page (home, category, profile, sermons, search) renders full bodies or audio links.
