# Project Plan

> Seeded by `/adopt` from the existing codebase on 2026-10-17. Sections 3, 4, 5,
> and 8 describe what already exists. Open questions were confirmed with the owner
> on 2026-10-17.

## 1. Problem - What problem are we solving?

Baptist pastors and writers need one home for their articles and sermons and a
way to build a following around them. Churches also need a place to advertise
open positions to that same community. Baptist Cloud brings writing, sermon
audio, author following, and a position board together in one place.

## 2. Users - Who is this for?

- **Authors** - Baptist pastors and writers who publish articles and upload sermons.
- **Readers** - members of the Baptist community who follow authors, read, listen,
  like, and report posts.
- **Churches** - post open positions on the position board. Anyone with a verified
  email can submit a listing; it stays hidden until an admin approves it, and
  posters can edit their own listings (planned, feature 16).
- **Admins** - manage users, ads, and reported posts.

## 3. Features - What does the MVP need?

Already shipped:

- Account registration, login, email verification, and password reset (Breeze)
- Profile editing with avatar upload, and public author profiles at `/@username`
- Markdown articles with a featured image, category, tags, drafts, and publishing
- A home feed of published posts, limited to followed authors when signed in
- Category browsing
- Follow and unfollow authors, with a follower list
- Post likes (rate limited)
- Reporting posts, with an admin review queue
- Sermons with MP3 audio, cover image, series, and tags
- Live search for posts, authors, and sermons (Livewire)
- Church position listings
- Site ads (admin-managed) and per-post author ads
- Admin dashboard: promote or revoke admins, delete users, manage ads
- Spark (Stripe) subscriptions that unlock author features in the post form
- Markdown learning sandbox
- Queued post image processing, monitored with Horizon

## 4. Data - What are we storing?

- **Users** - profile, username, avatar, `is_admin`, `is_author`, Spark billing columns
- **Posts** - title, slug, Markdown content, featured image, category, tags,
  author ad fields, `published_at`
- **Categories**
- **Followers** - user-to-user follow pivot
- **Likes** - user likes on posts
- **Reportings** - user reports against posts
- **Sermons** - title, slug, description, series, audio URL, video URL, image URL, tags, user
- **Listings** - church position: position, church, city, state, content, contact details
- **BcAds** - site-wide ads with an ordering integer
- **Subscriptions** - Spark / Cashier subscriptions, items, and tax rates
- Files on S3-compatible storage (IONOS): post images, sermon audio, and images

## 5. Tech - What stack are we using?

- PHP 8.2+, Laravel 12
- Blade views with Livewire 4 components and Alpine.js
- Tailwind CSS with `@tailwindcss/forms`, built with Vite
- MySQL
- Breeze auth scaffolding (laravel/ui is also installed)
- Laravel Spark (Stripe) for subscriptions, to be replaced by Laravel Cashier (Stripe)
  (planned, feature 17)
- Database queue with Laravel Horizon
- Intervention Image (GD) for image resizing
- Flysystem S3 for file storage on IONOS object storage
- Laravel Scout installed (search currently uses `LIKE` queries)
- Cloudflare Turnstile installed (currently disabled)
- Pest for tests, Larastan (level 5) for static analysis, Pint for formatting
- Laravel Herd for local development, Laravel Boost for AI tooling

## 6. Monetize - How will this make money?

- A $20 author subscription that lets authors place their own ad on their articles.
  Spark currently handles billing; it will move to Laravel Cashier (Stripe)
  (planned, feature 17)
- Site ads managed by admins

## 7. UI/UX - How should this look and feel?

Keep the current look for now: Breeze-style Blade components styled with Tailwind.
No redesign is planned.

## 8. Deployment - Where and how will this ship?

- PHP host behind LiteSpeed (a commit switched a multipart PATCH to POST to avoid
  LiteSpeed 405 errors)
- HTTPS is forced when `APP_URL` starts with `https://`
- A queue worker or Horizon process must run for post image processing
- Storage disks `postImages` and `sermons` point at IONOS S3
- Hosted on xCloud today, with a migration to Ploi planned. The deploy process and
  queue-worker supervision will be defined during the Ploi move.

## 9. Usage model and constraints (optional)

- Internet-facing, with public registration
- Rate limits on likes (3 per minute) and follows (5 per minute)
