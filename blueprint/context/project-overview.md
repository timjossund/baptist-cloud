# Baptist Cloud - Project Overview

<!-- blueprint:source-hash e16352b363ea8b2033e5866e05346407ae02490118b98544a3972eac71f328f3 -->

> A publishing and community platform where Baptist pastors and writers publish
> articles and sermons, readers follow them, and churches post open positions.

## Problem

Baptist pastors and writers need one home for their articles and sermons and a
way to build a following around them. Churches need a place to advertise open
positions to the same community. Baptist Cloud combines writing, sermon audio,
author following, and a position board.

## Users

- **Guests** - can see the home feed, category pages, and the sermon landing page.
- **Readers** (signed in, verified) - follow authors, read posts, listen to sermons,
  like, report, search, and view positions.
- **Authors** - publish articles and sermons. Author features in the post form
  (the per-post author ad) unlock with a paid subscription, `is_author`, or `is_admin`.
- **Churches** - post open positions. Planned (feature 16): any verified user can
  submit a listing, it stays hidden until an admin approves it, and posters edit
  only their own listings.
- **Admins** (`is_admin`) - manage users, admin roles, site ads, reported posts,
  and Horizon.

## Usage model

- Internet-facing, with public registration.
- Rate limits: likes 3 per minute, follows 5 per minute (by user id, else IP).

## Features

Features 1-15 are shipped. Feature 16 is next.

1. **Accounts and auth** - Breeze registration, login, email verification, password reset.
2. **Profiles** - profile editing with avatar, public author pages at `/@username`.
3. **Articles** (headline) - Markdown posts with featured image, category, tags, drafts, and publishing.
4. **Home feed and categories** - published posts by followed authors when signed in, plus category browsing.
5. **Following** - follow and unfollow authors, with a follower list.
6. **Likes** - rate-limited post likes.
7. **Post reporting** - users report posts, and admins review and clear the reports.
8. **Sermons** - MP3 sermons with cover image, series, tags, and a feed.
9. **Search** - live Livewire search for posts, authors, and sermons.
10. **Position listings** - church position board with create and edit.
11. **Ads** - admin-managed site ads and per-post author ads.
12. **Admin dashboard** - promote or revoke admins, delete users, manage ads.
13. **Subscriptions** - Spark (Stripe) billing that unlocks author features.
14. **Queued image processing** - post images resized on the queue, Horizon for admins.
15. **Re-enable Turnstile** - restore Cloudflare Turnstile on login and registration.
16. **Position approval** - verified users submit positions, admins approve before they go live, posters edit their own.
17. **Replace Spark with Cashier** - $20 author subscription via Laravel Cashier (Stripe) that unlocks per-article author ads.

## Data model

All tables have `id` and timestamps unless noted. These shapes are shipped and
in production, so change them only with new migrations.

### User

- `name`, `email` (unique), `email_verified_at`, `password`, `remember_token`
- `username` (string, unique) - used in `/@username` URLs
- `avatar` (string, nullable), `bio` (text, nullable)
- `is_admin`, `is_author` (bool, default false)
- Spark / Cashier billing columns (`stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at`); Cashier reuses them after feature 17
- has many Posts, Sermons, Likes; follows many Users through `followers`

### Post

- `title`, `slug` (unique, title plus 3 random chars), `content` (longText, Markdown)
- `image` (string, nullable) - file name on the `postImages` disk; the accessor returns the full IONOS S3 URL
- `tags` (string, nullable), `published_at` (timestamp, nullable; null means draft)
- `ad_heading`, `ad_description`, `ad_link` (string, nullable) - author ad
- belongs to Category (`category_id`, cascade) and User (`user_id`, cascade)
- has many Likes and Reportings

### Category

- `title` - route-bound by title at `/category/{title}`
- has many Posts

### Follower (pivot)

- `user_id` (the followed author), `follower_id` (users), `created_at` only

### Like

- `post_id`, `user_id` (cascade), `created_at` only

### Reporting

- `reporter_name`, `reporter_email`, `description` (longText)
- `post_id`, plus denormalized `post_title`, `post_slug`, `username`
- belongs to Post

### Sermon

- `title`, `slug` (unique), `description` (text), `series_title` (nullable), `tags` (nullable)
- `audio_url` (MP3 on the `sermons` disk), `video_url` (nullable), `image_url` (nullable)
- belongs to User (`user_id`)

### Listing

- `position`, `church`, `city`, `state`, `content` (longText)
- `email`, `phone`, `facebook` (nullable), `website` (nullable)
- `published_at` (nullable)
- no owner column today; feature 16 needs an owner (`user_id`) and an approval
  state so pending listings stay hidden and posters can edit their own

### BcAd (`bc_ads`)

- `title`, `description`, `link`, `int` (nullable ordering integer)

### Subscriptions

- Spark / Cashier `subscriptions`, `subscription_items`, `tax_rates`; Cashier keeps
  using these after feature 17, so existing Spark subscribers must carry over

## Tech stack

- **Laravel 12 / PHP 8.2+** - application framework
- **Blade + Livewire 4 + Alpine.js** - server-rendered UI with interactive search, post form, and Markdown sandbox
- **Tailwind CSS + Vite** - styling and asset build
- **MySQL** - primary database (SQLite in memory for tests)
- **Breeze** - auth scaffolding (laravel/ui also installed)
- **Laravel Spark (Stripe)** - subscriptions today; replaced by **Laravel Cashier (Stripe)** in feature 17
- **Database queue + Horizon** - background jobs and monitoring
- **Intervention Image (GD)** - post image resizing (1200x400 JPEG)
- **Flysystem S3 on IONOS** - post images and sermon audio and images
- **Laravel Scout** - installed; search currently uses `LIKE` queries
- **Cloudflare Turnstile** - on login and registration (feature 15)
- **Pest, Larastan level 5, Pint** - tests, static analysis, formatting

## Monetization

- A $20 author subscription that lets authors place their own ad on their
  articles. Spark bills it today; feature 17 moves it to Laravel Cashier.
- Admin-managed site ads

> TODO: billing interval for the $20 plan (the Spark config has a monthly price only).

## UI/UX

Keep the current Breeze-style Blade components styled with Tailwind. No redesign
is planned.

Main routes:

- `/` - home feed; `/category/{title}` - category feed
- `/sermons` - sermon landing; `/sermons/feed`, `/sermons/create`, `/sermons/{sermon}`
- `/@{username}` - public profile; `/@{username}/{slug}` - single post
- `/post/create-post`, `/post/{slug}/edit` - author editor; `/learn-markdown` - sandbox
- `/search`, `/search-audio`, `/search-authors`, `/search-posts`
- `/positions`, `/position/{id}`, `/create-listing`
- `/profile`, `/follower-list`
- `/admin`, `/admin/reported-posts`, `/create-ad`, `/horizon` - admin only

## Deployment

- PHP host behind LiteSpeed. Multipart forms use POST rather than PATCH to avoid 405 errors.
- HTTPS is forced when `APP_URL` starts with `https://`.
- A queue worker or Horizon process must run for post image processing.
- S3 disks `postImages` and `sermons` (IONOS); temporary uploads on `local`.

- Hosted on xCloud today; a migration to Ploi is planned.

> TODO: the Ploi deploy process and queue worker (Horizon) supervision, to be
> defined during the move.

## Open questions

- Billing interval for the $20 author plan, and how existing Spark subscribers
  move to Cashier (feature 17).
- Ploi deploy and queue-worker details are not defined yet.
- The plans list users reporting posts, but `reportings` stores the reporter's
  name and email with no `user_id`.
- Two tables are absent from both plans: an empty `author_ads` table (author ads
  live on `posts`) and a Spatie-style `media` table whose model code is
  commented out.
- `project-plan.md` §5 still says Turnstile is "currently disabled"; feature 15
  re-enabled it.
