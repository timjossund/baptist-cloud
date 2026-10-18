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
- A JSON API for the phone app under `/api/v1`, using Laravel Sanctum (see spec 0002). `POST /api/v1/auth/token` trades an email, password, and `device_name` for a token for that device. `GET /api/v1/me` returns the signed in user's `id`, `name`, `username`, `avatar_url`, `plan`, and `has_full_access`. `DELETE /api/v1/auth/token` signs out that one device. Free users can sign in too. Only verified accounts get a token.
- Every error under `/api/` comes back as JSON `{ "message", "code" }` with a stable code the app can react to: `validation_failed`, `invalid_credentials`, `email_unverified`, `unauthenticated`, `plan_required`, `too_many_attempts`, `too_many_requests`, `not_found`, `http_error`, or `server_error`.
- A `full.access` route middleware that refuses users without a plan with `403 plan_required`, ready for the sermon API.
- API tokens stop working after 90 days without use, and a daily scheduled job (`prune-idle-api-tokens`) deletes them. Production needs `php artisan migrate` for the new `personal_access_tokens` table.

### Changed
- The Author plan is now $25/month for new subscribers. Subscribers on a legacy $20 price listed in `STRIPE_AUTHOR_LEGACY_PRICES` keep their price and every Author feature. A subscription on an unknown price is treated as Author and logs a warning.
- Author ads are available only to admins, `is_author` users, and Author or legacy Author subscribers. The Member plan does not unlock them.
- Subscribing now requires a `plan` of `member` or `author`. Any other value fails validation, the price always comes from config, and existing subscribers are sent back to `/billing` (the Stripe portal) instead of getting a second subscription.
- Subscriptions that are past due while Stripe retries the card keep full access.
- Sermon audio now plays from a signed link that expires 6 hours after the page loads, and the `sermons` disk uses path style URLs.
- `/sermons/feed` no longer has inline audio players. Each sermon links to its own page instead.
- Changing your password on the profile page, resetting it from the email link, or deleting your account now signs the phone app out on every device.

### Security
- Paywalled pages never include the full article body or a sermon's audio link, and no listing page (home, category, profile, sermons, search) renders full bodies or audio links.
- API sign in is rate limited three ways: 5 failures a minute for one email and IP, 20 attempts a minute from one IP, and 10 failures in 15 minutes for one email from any IP. A blocked attempt gets `429 too_many_attempts` with a `Retry-After` header, and only the IP and the limit name are logged.
- Signed in API requests are limited to 120 a minute per user, shared across that user's devices. Requests refused with `401` or `403` don't count.
- A wrong email and a wrong password get the same response and take about the same time, so the API doesn't reveal which emails have accounts. `email_unverified` only appears after a correct password.
- API tokens are stored hashed, API responses never include the user's email, and the API sets no cookies and never accepts a website session in place of a token.
