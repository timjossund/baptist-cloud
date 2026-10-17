# Fix: Send payment confirmation emails

**Type:** Fix
**Status:** verified
**Branch:** fix/send-payment-confirmation-emails
**Fixes:** F-01

## The problem

Spark emailed subscribers when a renewal needed extra card confirmation (3D
Secure), because the deleted `config/spark.php` enabled
`Features::paymentNotificationEmails()`. Cashier only sends that email when
`config('cashier.payment_notification')` is set. Cashier's
`WebhookController::handleInvoicePaymentActionRequired` returns early when it's
null, and nothing sets it now.

So after feature 17, a renewal that needs confirmation goes to
`past_due`/`incomplete`, the author ad disappears, and the subscriber hears
nothing from the app.

## The fix

Turn Cashier's built-in notification on by default, which matches Spark's
behavior:

- Add a minimal `config/cashier.php` containing only `'payment_notification'
  => env('CASHIER_PAYMENT_NOTIFICATION') ?:
  \Laravel\Cashier\Notifications\ConfirmPayment::class`. The `?:` makes a
  blank env value fall back to the default. Plain `env(..., default)` would
  return `''`, and Cashier would try to build a class named `''`.
  - Cashier's `mergeConfigFrom` fills in every other Cashier key from the
    package, so nothing else changes.
  - Setting `CASHIER_PAYMENT_NOTIFICATION` in env can still override it.
- List `CASHIER_PAYMENT_NOTIFICATION` in `.env.example` as a commented-out
  line, explaining that the default is `ConfirmPayment`.

Existing pieces it reuses, so no new routes or views are needed:

- `ConfirmPayment` is a queued mail notification (`ShouldQueue`), which the
  existing queue worker or Horizon sends.
- Its button links to Cashier's built-in `stripe/payment/{id}` confirm page.
  That route is already registered.

It must not break anything:

- No app code changes. Billing pages, webhooks, and `canRunAds()` behave the
  same.
- Checkout's on-session payments are still skipped by Cashier itself, through
  its `is_on_session_checkout` metadata check.

## Build steps

1. Add `config/cashier.php` and the `.env.example` entry, plus tests in
   `tests/Feature/BillingTest.php`. **Done when:**
   - `config('cashier.payment_notification')` is `ConfirmPayment::class` by
     default.
   - The other Cashier config keys still resolve from the package, for
     example `cashier.path` is `stripe`.
   - A `ConfirmPayment` built from an offline
     `Stripe\PaymentIntent::constructFrom([...])` renders a mail whose action
     URL points at `/stripe/payment/<id>`.
   - `composer test` passes.

## Verify

- `composer test`, `vendor/bin/pint --test`, and `vendor/bin/phpstan analyse`
  are clean.
- Manual, optional: with Stripe test keys and `stripe listen`, trigger
  `invoice.payment_action_required` for a subscribed test customer. A
  "Confirm Payment" email then appears in the log mailer locally.
- Deploy note: production needs working mail and a running queue worker.
  Horizon already runs there for image processing.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":2960,"specSha256":"3700a5b238b48c5fb91240079bc8275b6b64eaaeda4adcc579a52ee0f38f1afd","branch":"refs/heads/fix/send-payment-confirmation-emails","head":"f0524568931c8cfa1c8b60bf1b7c216dbf832512","baseRef":"refs/heads/main","baseCommit":"f0524568931c8cfa1c8b60bf1b7c216dbf832512","sourceTree":"97765b6d7129c63b1ed057faea17e7ef31dc9219","landing":"local-merge","absentOptional":[]} -->

## Findings

### send-payment-confirmation-emails/F-01 [P3] closed - Spark payment-confirmation emails stop with no replacement noted

**File:** config/spark.php:91 (deleted in this change)
**Found:** 2026-10-17 by /audit independent (scope: current; lens: quality)
**Why it matters:** The removed Spark config enabled `Features::paymentNotificationEmails()`, so subscribers were emailed when a renewal needed extra payment confirmation (SCA/3DS). Cashier sends that notification only when `CASHIER_PAYMENT_NOTIFICATION` is set (vendor/laravel/cashier/src/Http/Controllers/WebhookController.php:292). Neither `.env.example` nor the spec's deploy actions mention it or Stripe's own customer emails. A renewal that needs confirmation would lapse to past_due/incomplete, and the author ad would disappear with no notice from the app.
**Suggested fix:** Add one deploy action: either enable Stripe's "payment requires authentication" customer emails in the Stripe dashboard, or set `CASHIER_PAYMENT_NOTIFICATION=Laravel\Cashier\Notifications\ConfirmPayment` and list it in `.env.example`. No code change is required.
**Resolution:** Fixed by fix/send-payment-confirmation-emails. A new `config/cashier.php` sets `payment_notification` to Cashier's `ConfirmPayment` by default, and a blank env value also falls back to it. Tests cover the default and the rendered mail link. Re-reviewed 2026-10-17 by /audit (scope: current; lenses: quality, security, performance, tests). `php artisan config:show cashier` resolves `payment_notification` to `ConfirmPayment`, with the package defaults intact. A blank env value falls back to the default. 89 tests pass, including the two new ones. No new defect found. Closed.

## Try it now

Run or do:

```bash
php artisan config:show cashier.payment_notification
```

Expected:

`Laravel\Cashier\Notifications\ConfirmPayment`

This proves:

Cashier will email subscribers when a renewal needs extra payment confirmation.
