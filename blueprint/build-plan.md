# Build Plan

Seeded by `/adopt` from the existing codebase. Checked items are already shipped.
Keep each item to one line; the details come later in `/feature`.

Run `/feature` to spec the next unchecked item, or `/feature 15` to pick one.
Keep completed items checked and append new features as the project grows.
Do not renumber completed features; their archived specs refer to those IDs.

## Your features

- [x] 1. **Accounts and auth** - registration, login, email verification, password reset (Breeze)
- [x] 2. **Profiles** - profile editing with avatar upload, public author pages at `/@username`
- [x] 3. **Articles** - Markdown posts with featured image, category, tags, drafts, and publishing
- [x] 4. **Home feed and categories** - published post feed, followed authors when signed in, category browsing
- [x] 5. **Following** - follow and unfollow authors, follower list
- [x] 6. **Likes** - rate-limited post likes
- [x] 7. **Post reporting** - users report posts, admins review and clear reports
- [x] 8. **Sermons** - MP3 sermons with cover image, series, tags, and feed
- [x] 9. **Search** - live Livewire search for posts, authors, and sermons
- [x] 10. **Position listings** - church position board with create and edit
- [x] 11. **Ads** - admin-managed site ads and per-post author ads
- [x] 12. **Admin dashboard** - promote or revoke admins, delete users, manage ads
- [x] 13. **Subscriptions** - Spark (Stripe) billing that unlocks author features
- [x] 14. **Queued image processing** - post images resized on the queue, Horizon for admins
- [x] 15. **Re-enable Turnstile** - restore Cloudflare Turnstile on login and registration
- [ ] 16. **Position approval** - verified users submit positions, admins approve before they go live, posters edit their own
- [ ] 17. **Replace Spark with Cashier** - $20 author subscription via Laravel Cashier (Stripe) that unlocks per-article author ads
