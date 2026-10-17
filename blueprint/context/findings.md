# Findings

> **Generated file.** The findings ledger: review findings raised by `/audit`
> against the work in progress, each with a durable ID, severity (P0-P3), and
> status. `/implement` marks repaired findings `fixed`, a later `/audit` pass
> moves them to `closed`, and `/complete` refuses to land while any P0 or P1
> finding is `open` or `fixed`, then archives resolved findings with the work
> and resets this file.

### F-01 [P3] open - Spark payment-confirmation emails stop with no replacement noted

**File:** config/spark.php:91 (deleted in this change)
**Found:** 2026-10-17 by /audit independent (scope: current; lens: quality)
**Why it matters:** The removed Spark config enabled `Features::paymentNotificationEmails()`, so subscribers were emailed when a renewal needed extra payment confirmation (SCA/3DS). Cashier sends that notification only when `CASHIER_PAYMENT_NOTIFICATION` is set (vendor/laravel/cashier/src/Http/Controllers/WebhookController.php:292). Neither `.env.example` nor the spec's deploy actions mention it or Stripe's own customer emails. A renewal that needs confirmation would lapse to past_due/incomplete, and the author ad would disappear with no notice from the app.
**Suggested fix:** Add one deploy action: either enable Stripe's "payment requires authentication" customer emails in the Stripe dashboard, or set `CASHIER_PAYMENT_NOTIFICATION=Laravel\Cashier\Notifications\ConfirmPayment` and list it in `.env.example`. No code change is required.
**Resolution:**

### F-02 [P3] open - Portal error redirects to home, not /billing as the spec states

**File:** app/Http/Controllers/BillingController.php:27
**Found:** 2026-10-17 by /audit independent (scope: current; lens: quality)
**Why it matters:** The spec says Stripe errors on "checkout or the portal redirect back to `/billing`". On the portal path, the code redirects to `/` instead. The code is the safer choice, because for a subscribed user `/billing` would retry the portal and could loop. Still, the spec and the code now disagree.
**Suggested fix:** Change the spec wording to say a portal error redirects to `/` with the error flash. Keep the code as is.
**Resolution:**

### F-03 [P3] open - Webhook CSRF test cannot detect a CSRF regression

**File:** tests/Feature/BillingTest.php:41
**Found:** 2026-10-17 by /audit independent (scope: current; lens: tests)
**Why it matters:** Laravel's `VerifyCsrfToken` turns itself off whenever unit tests run (vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php:83). This test would still pass if the webhook moved into the `web` group. It proves the unknown-event 200 path, not the CSRF exemption. `php artisan route:list` confirms the real route has no `web` middleware today.
**Suggested fix:** Rename the test to describe what it proves, or assert that the `cashier.webhook` route's gathered middleware does not include `web`.
**Resolution:**
