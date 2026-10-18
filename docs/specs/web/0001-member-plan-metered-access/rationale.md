# 0001. Member plan: rationale

Decision record for [index.md](index.md). `/develop` doesn't need to read this.

## Context

Baptist Cloud sells one $20/month Author plan through Laravel Cashier (Stripe). It unlocks author ads. Articles and sermons are free to any signed in, verified user. The coming phone app (audio-app) needs a paid plan that people buy on the website, since Apple's reader app rule lets an audio app sign people in to a subscription bought elsewhere without selling in the app.

The owner wants a $15/month plan for reading and listening, a $25 Author plan for new subscribers, current $20 subscribers left alone, and a metered paywall on the website: 5 free articles and 2 free sermons a month, per account.

Forces:
- Today the code treats "has any subscription" as "is an author" (`canRunAds()` uses `subscribed()`). Adding a second plan without changing this would hand author ads to $15 members.
- Sermon audio is a plain public link to IONOS object storage, and `/sermons/feed` embeds players directly. A page level limit can't hold while audio is reachable that way.
- The mobile API (web 20) needs one rule it can call to decide who listens.
- Small team, no current scheduler, Pest feature tests with Stripe and S3 faked.

## Options considered

### Option 1: One subscription, tier from the Stripe price, plus a DB view meter (chosen)

Keep the `default` subscription. Map price IDs to tiers in config (including legacy prices). Count distinct items per user per month in a table.

**Pros**: plan switching in the Stripe portal just swaps the price, with nothing to sync. Legacy prices map cleanly. The meter is exact, per account, and easy to test.
**Cons**: price IDs live in env and must be kept correct. Adds one table and a scheduled prune.

### Option 2: Two named subscriptions (`member`, `author`)

A separate Cashier subscription per plan.

**Pros**: the tier is visible from the subscription name alone.
**Cons**: one person can end up holding both. Upgrading means canceling one and starting another, and the portal can't switch between them. It also needs extra rules to stop doubles.

### Option 3: A `plan` column on users, synced from webhooks

Store the tier on the user and update it from Stripe webhook events.

**Pros**: a simple read everywhere.
**Cons**: it duplicates state Cashier already keeps. A missed or out of order webhook leaves the column wrong, and past due and grace period logic would have to be rebuilt by hand.

### Option 4: Meter by session or cookie instead of a table

**Pros**: no table.
**Cons**: clearing cookies resets the count, and it doesn't follow the person across devices. Every metered page already requires sign in, so there's no reason to accept the leak.

## Rationale

Cashier already stores the Stripe price on each subscription and handles active, past due, and grace period states. Reading the tier from the price reuses all of that and makes Stripe portal switching work for free (Option 2 breaks it, and Option 3 duplicates it). The legacy price list is what keeps current $20 authors whole without touching their subscriptions. Every metered route is already behind `auth` and `verified`, so a per account table (Option 4's weakness) is cheap and exact. The unique row per item per month gives "reopening is free" without extra logic. Signed audio links are the smallest change that makes the sermon limit real, and the mobile API needs the same thing anyway.

Small calls made while writing:
- **Preview**: the first 3 Markdown blocks, rendered with the existing `Markdown` helper. Runner up: trimming the rendered HTML, which risks broken tags.
- **Link lifetime**: 6 hours, which covers long sermons with pauses. Runner up: 2 hours.
- **Meter location**: `App\Support\ContentMeter`, next to the existing `Support\Markdown`. Runner up: methods on `User`, which would bloat the model.
- **Pruning**: the `Prunable` trait plus `model:prune` daily. Runner up: a custom command.
- **Race between tabs**: accept one possible extra view. Runner up: a row lock, which isn't worth it here.
- **Past due**: `Cashier::keepPastDueSubscriptionsActive()`, matching the "keep access while Stripe retries" choice.
- **Unknown prices count as Author**: losing ads by accident would hurt paying authors more than a stray price getting ads would hurt the site. A logged warning makes it visible. Runner up: Free.
- **Plain `kind` column instead of a morph map**: an enforced morph map is global and the `media` table already uses morphs, and nothing reads the view back to its item. Runner up: a non enforcing `morphMap`.
- **One cached `planTier()`**: one subscription lookup per request instead of two or three.

Cross check (another model, 2026-10-17): found 23 completeness gaps and 9 soundness notes. All recommended fixes were applied with your approval. A hover prefetch concern was checked and doesn't apply (no `wire:navigate.hover` in the views).
