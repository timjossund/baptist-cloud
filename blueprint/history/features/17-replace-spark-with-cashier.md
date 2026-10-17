# Feature: Replace Spark with Cashier

**From build-plan:** feature 17
**Build attempt:** 1
**Branch:** feature/replace-spark-with-cashier
**Status:** verified

## Goal

Remove Laravel Spark and run the author subscription directly on Laravel
Cashier (Stripe). The plan is one $20-a-month author subscription. It unlocks
the per-article author ad, exactly as Spark does today. Existing subscribers
stay subscribed, with no data migration. Spark's built-in billing portal
moves to Stripe-hosted pages: Stripe Checkout to subscribe, and the Stripe
Customer Portal to manage or cancel.

## In scope

- Swap the dependency. Remove `laravel/spark-stripe` and its composer
  repository, and require `laravel/cashier` directly. It is already installed
  as v15.8.0 underneath Spark.
- `User` uses `Laravel\Cashier\Billable` instead of `Spark\Billable`.
- Delete everything Spark-only: `SparkServiceProvider` and its registration,
  `config/spark.php`, and `resources/views/vendor/spark/`.
- The Stripe price ID for the monthly plan comes from the new
  `services.stripe.author_price` config key, read from the
  `STRIPE_AUTHOR_PRICE` env var.
- `/billing` keeps its URL, so both "Manage Billing" navigation links still
  work:
  - A **subscribed** user (including one in a cancellation grace period) is
    redirected to the Stripe Customer Portal. The portal returns them to `/`.
  - An **unsubscribed** user sees a simple page: "Author subscription:
    $20/month", one sentence on what it unlocks (your own ad on your articles),
    and a Subscribe button.
- `POST /billing/subscribe` starts Stripe Checkout for the `default`
  subscription. Success returns to `/billing/success`, which redirects to `/`
  with a "Thanks for subscribing" flash. Cancel returns to `/billing`.
- Stripe API errors on checkout or the portal redirect back to `/billing` with
  an error flash. They never show a 500 page.
- Cashier's webhook at `POST /stripe/webhook` replaces Spark's
  `/spark/webhook`. It keeps local subscription rows in sync, using
  `STRIPE_WEBHOOK_SECRET` signature checks when that variable is set.
- Admin users table: replace the broken `$user->subscription_id` check (no
  such column, so every non-admin shows "Free") with `$user->subscribed()`.
  Eager-load `subscriptions` on that paginated list.

## Out of scope

- More plans, yearly billing, trials, coupons, or tax settings. More plans may
  come later.
- Dropping the Spark-only user columns (`billing_address`, `vat_id`,
  `invoice_emails`, and so on) or the `tax_rates` table. Leave stored data
  untouched.
- Changing what a subscription unlocks, the post-form ad fields, or `canRunAds()`
  logic
- Custom invoice pages, emails, or receipts. The Stripe portal covers these.
- Changing anything in the Stripe dashboard. That's your action; see Notes.

## Build loop

`workflow.stepReview` is `feature`, so build all steps, then present one review
packet. `workflow.checkpointCommits` is `disabled`. `/complete` creates the
single feature commit. `composer test` must pass at the end of each logic step.

## Build steps

- [x] **1. Swap Spark for Cashier.** Run `composer remove
  laravel/spark-stripe` and `composer require laravel/cashier:^15.8`, and
  remove the Spark repository entry from `composer.json`. Switch `User` to
  `Laravel\Cashier\Billable`. Delete `app/Providers/SparkServiceProvider.php`,
  its line in `bootstrap/providers.php`, `config/spark.php`, and
  `resources/views/vendor/spark/`. Add `'author_price' =>
  env('STRIPE_AUTHOR_PRICE')` under `stripe` in `config/services.php`. Add
  empty `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, and
  `STRIPE_AUTHOR_PRICE` lines to `.env.example`. **Done when:** `composer
  test` passes. `php artisan route:list` shows `stripe/webhook` and no `spark/`
  routes. New tests show that:
  - a user with an existing active `default` subscription row (the shape
    Spark wrote) is `subscribed()` and `canRunAds()`
  - a canceled one past `ends_at` is not
  - an unsigned unknown-event POST to `/stripe/webhook` returns 200 with no
    CSRF error (no secret is set in tests)

- [x] **2. Billing pages.** Add a `BillingController` with routes in the
  `auth` + `verified` group:
  - `GET /billing` (`billing`): a subscribed user goes to the portal;
    otherwise render a new `resources/views/billing.blade.php` in the app
    layout.
  - `POST /billing/subscribe` (`billing.subscribe`): an already subscribed
    user is redirected to `/billing`; otherwise start `newSubscription('default',
    config('services.stripe.author_price'))->checkout([...])`.
  - `GET /billing/success` (`billing.success`): redirects to `/` with a success
    flash.

  Catch `Laravel\Cashier\Exceptions\IncompletePayment` and
  `Stripe\Exception\ApiErrorException` around Stripe calls, and redirect to
  `/billing` with "Billing is unavailable right now. Please try again." When the
  price config is empty, the Subscribe action shows that same error instead of
  calling Stripe. **Done when:** tests cover:
  - a guest being redirected to login
  - an unsubscribed user seeing the $20/month page with a Subscribe button
  - an unsubscribed user with no price configured getting the error flash
  - an already subscribed user who posts subscribe being redirected to `/billing`
  - the success route flashing and redirecting to `/`

  The real Checkout and Portal redirects are checked by hand with Stripe test
  keys, not in tests.

- [x] **3. Admin subscriber label.** In `components/users-table.blade.php`,
  show "Author" when `$user->subscribed()` is true. In `AdminController@index`,
  eager-load `subscriptions`. **Done when:** a test shows a subscribed user is
  labeled "Author" and an unsubscribed one "Free" on `/admin`.

## Files / areas

- `composer.json`, `composer.lock`
- `app/Models/User.php`
- `app/Providers/SparkServiceProvider.php` (delete), `bootstrap/providers.php`
- `config/spark.php` (delete), `config/services.php`, `.env.example`
- `resources/views/vendor/spark/` (delete)
- `app/Http/Controllers/BillingController.php` (new), `routes/web.php`
- `resources/views/billing.blade.php` (new)
- `app/Http/Controllers/AdminController.php`, `resources/views/components/users-table.blade.php`
- `tests/Feature/BillingTest.php` (new)

## Data / contracts

- No migrations. Spark already stores Cashier's schema: `users.stripe_id`,
  `pm_type`, `pm_last_four`, `trial_ends_at`, plus `subscriptions` (`type`,
  `stripe_id`, `stripe_status`, `stripe_price`, `quantity`, `ends_at`) and
  `subscription_items`. Existing rows keep working as-is.
- The subscription type is `'default'`, the same one Spark used, so
  `subscribed()` keeps recognizing existing subscribers.
- The price comes from Stripe. The app only shows the text "$20/month" and
  sends `STRIPE_AUTHOR_PRICE`. It never sends an amount.
- The trusted actor is always `auth()->user()`. No route takes a user ID.
- Checkout and portal URLs come from Stripe responses and are used only as
  redirects. Nothing from Stripe is rendered as HTML.
- Webhook: Cashier's controller at `/stripe/webhook`, outside the `web`
  middleware group, so CSRF doesn't apply. It verifies the signature when
  `STRIPE_WEBHOOK_SECRET` is set.

## Testing

Pest feature tests in `tests/Feature/BillingTest.php`, plus the existing
`PostAdsTest`, which must keep passing. Subscription state is created by
inserting `subscriptions` rows directly, with no Stripe calls. Stripe Checkout,
the Customer Portal, and live webhooks aren't tested automatically. They're a
manual check with Stripe test-mode keys at http://baptist-cloud.test. Run
Larastan and Pint before the review packet.

## Notes for the AI

- Don't publish Cashier's migrations. The tables already exist.
- `config/cashier.php` doesn't need publishing. Cashier reads `STRIPE_KEY`,
  `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, and `CASHIER_CURRENCY` from env
  by default.
- **Your actions at deploy time**, listed in the review packet and not done
  by the agent:
  1. Set `STRIPE_AUTHOR_PRICE` to the existing monthly price ID (today's
     `SPARK_STANDARD_MONTHLY_PLAN` value).
  2. In the Stripe dashboard, change the webhook endpoint URL from
     `/spark/webhook` to `/stripe/webhook`. Keep the same signing secret and
     the subscription and customer events.
  3. Enable the Stripe Customer Portal, allowing cancel and payment method
     updates.
  4. Remove the Spark license and composer auth from the server if present.

  Between the deploy and step 2, webhook events will fail and Stripe will
  retry them. Do step 2 right after deploying.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":8575,"specSha256":"3dbdbf97e71360fcc113aee0a00f92947294ba3d440336beb3a2e12b92ca52e8","branch":"refs/heads/feature/replace-spark-with-cashier","head":"92856a6414be0f8b987012ce3a4c3e5632798aad","baseRef":"refs/heads/main","baseCommit":"4e4ec7573961e3b898281173aaa7afe0b046b681","sourceTree":"caa6b9087ebda113894fe6b92114f430aec5c37b","landing":"local-merge","absentOptional":[]} -->

## Independent review

**Status:** passed
**Target commit:** 92856a6414be0f8b987012ce3a4c3e5632798aad
**Base commit:** 4e4ec7573961e3b898281173aaa7afe0b046b681
**Base ref:** main
**Spec hash:** 3dbdbf97e71360fcc113aee0a00f92947294ba3d440336beb3a2e12b92ca52e8
**Prepared by:** claude
**Builder model:** claude-opus-5-5
**Requested reviewer:** claude
**Requested model:** claude-opus-5-5
**Requested execution:** automatic
**Requested at:** 2026-10-17T20:05:27Z
**Workflow:** regular
**Check required:** no
**Reviewer adapter:** claude
**Reviewer model:** claude-opus-5-5
**Reviewer context:** fresh subagent
**Actual execution:** automatic
**Reviewed at:** 2026-10-17T20:07:35Z
**Scope:** current
**Lenses:** quality, security, performance, tests
**Verdict:** passed
**Check result:** not-required

### Commands

- `composer test`: pass (87 tests, 247 assertions)
- `vendor/bin/phpstan analyse --no-progress`: pass (no errors)
- `vendor/bin/pint --test`: pass
- `php artisan route:list -v --path=stripe` / `--path=spark` / `--path=billing`: pass (`stripe/webhook` present without `web`/CSRF, with `VerifyWebhookSignature`; no `spark/` routes; billing routes under `web`, `auth`, `verified`)
- `composer validate --no-check-publish --no-check-all`: pass (lock in sync)

### Evidence

- Phase B preflight: HEAD = target, `git merge-base main HEAD` = base, spec SHA-256 matches, spec tracked (no snapshot), only `blueprint/context/review.md` dirty.
- Existing `subscriptions`, `subscription_items`, and `users` billing columns (database/migrations/2025_05_17_2107*) match Cashier v15.8.0's schema; `subscribed()` resolves `type = 'default'` rows, covered by BillingTest.
- Cashier 15 only publishes its migrations (CashierServiceProvider.php:133-143), so removing Spark does not cause duplicate-column migrations on deploy.
- No app, config, view, route, or test code references Spark or the removed lock packages (dompdf, inertia, vat-calculator, masterminds/html5, sabberworm, thecodingmachine/safe); `vendor/` no longer contains Spark.
- BillingController uses only `auth()->user()`, catches `ApiErrorException` around Checkout and portal calls, guards an empty price, and renders no Stripe data. `IncompletePayment` is not thrown by `checkout()` or the portal, so not catching it is correct.
- Admin users list eager-loads `subscriptions`; Cashier's `subscription()` filters the loaded collection, so there is no N+1.

### Findings

- F-01 [P3] open: Spark payment-confirmation emails stop; deploy notes omit `CASHIER_PAYMENT_NOTIFICATION` or Stripe customer emails
- F-02 [P3] open: portal error redirects to `/`, while the spec says `/billing` (code is correct; spec wording drifts)
- F-03 [P3] open: webhook CSRF test cannot detect a CSRF regression, because CSRF is skipped in unit tests

### Remaining risk

- Real Stripe Checkout, Customer Portal, and live/signed webhook delivery were not exercised (no Stripe calls by design); the spec assigns this to a manual test-mode check.
- The `ApiErrorException` fallback paths in BillingController have no automated tests.
- Between returning from Checkout and the `customer.subscription.created` webhook, the user is not yet `subscribed()` and could start a second Checkout from `/billing`.
- Webhook gaps between deploy and the Stripe endpoint URL change (spec deploy step 2) are expected to be covered by Stripe retries.
- No combined Verify command or CI exists; `/check` was not required and was not run.

## Try it now

Run or do:

Sign in as a user without a subscription and open http://baptist-cloud.test/billing.

Expected:

A page titled "Author subscription" showing "$20/month", the line "Subscribe to place your own ad at the top of your articles. Cancel anytime.", and a Subscribe button.

This proves:

`/billing` is now served by the app's own Cashier-backed billing page instead of Spark.
