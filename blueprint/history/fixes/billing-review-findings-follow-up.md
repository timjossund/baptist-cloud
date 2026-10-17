# Fix: Billing review findings follow-up

**Type:** Fix
**Status:** verified
**Branch:** fix/billing-review-findings-follow-up
**Fixes:** F-02, F-03

## The problem

Two P3 findings from Feature 17's independent review are still open:

- **F-02, spec drift.** The archived Feature 17 spec
  (`blueprint/history/features/17-replace-spark-with-cashier.md`, the
  "Stripe API errors" bullet) says errors on checkout *or the portal* redirect
  to `/billing`. `BillingController::index` (line 27) sends a portal error to
  `/` (`home-page`) with the error flash. That is correct: for a subscribed
  user, `/billing` would retry the portal and could loop. The spec wording is
  what's wrong.
- **F-03, weak test.** In `tests/Feature/BillingTest.php`, the test
  `the stripe webhook accepts posts without a csrf token` can't fail on a CSRF
  regression, because `VerifyCsrfToken` skips itself while unit tests run. It
  would still pass if `POST /stripe/webhook` gained the `web` middleware group.
  All it proves is that an unknown event returns 200.

## The fix

- **F-02:** Correct the spec bullet so checkout errors redirect to `/billing`
  and portal errors redirect to `/`, both with the error flash and never a
  500. This changes the doc only. Leave the controller as it is.
- **F-03:** Keep the existing unknown-event 200 request. Also assert that the
  `cashier.webhook` route's gathered middleware does not include `web`, for
  example
  `expect(Route::getRoutes()->getByName('cashier.webhook')->gatherMiddleware())->not->toContain('web')`.
  Rename the test so the name matches what it checks (e.g. `the stripe webhook
  sits outside the web middleware group and accepts unknown events`).

Must not break: the other billing tests, the webhook route as Cashier
registers it, and the controller's behavior. Production code doesn't change.

## Build steps

- [x] 1. **Align the spec and harden the webhook test.** Make both changes
  above.
  Done when: the Feature 17 spec bullet describes the two redirect targets
  correctly, the renamed test asserts both the 200 and the missing `web`
  middleware, adding `'web'` to that route's middleware by hand makes the test
  fail (check it, then revert), and `composer test` passes.

## Verify

- `php artisan test --filter=BillingTest` passes.
- Temporarily put the webhook route in the `web` group, then confirm the test
  fails. Revert the change afterward.
- Read the corrected bullet in the Feature 17 history file.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":2468,"specSha256":"a4aefdcacf6d046883b7b4b03f5456289ff56930f1bbd53e213b8dbabab31bfd","branch":"refs/heads/fix/billing-review-findings-follow-up","head":"380934854fa6e40f49bb5d008eb6ef62080ededf","baseRef":"refs/heads/main","baseCommit":"380934854fa6e40f49bb5d008eb6ef62080ededf","sourceTree":"448d234a19cffa1b44258715115a50d642287cb9","landing":"local-merge","absentOptional":[]} -->
