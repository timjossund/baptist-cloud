# Fix: Turnstile outage returns a 500

**Type:** Fix
**Status:** verified
**Branch:** fix/turnstile-outage-returns-a-500
**Fixes:** F-03

## The problem

In production, login (`LoginRequest::rules()`) and registration
(`RegisteredUserController::store`) validate `cf-turnstile-response` with
`['bail', 'required', 'string', Rule::turnstile()]`. The package's
`TurnstileClient::siteverify` calls `Http::retry(3, 100)`, which throws by
default. After three non-2xx responses it throws `RequestException`, and a
network failure throws `ConnectionException`. The package's own failed-response
fallback (`! $response->ok()`) therefore never runs. When Cloudflare's siteverify
is down or unreachable, every login and registration POST returns a 500 page
instead of a visible error on the form. Users are blocked either way during an
outage, so this is error handling, not a bypass.

## The fix

Add one small validation rule, `app/Rules/Turnstile.php`, that implements
`Illuminate\Contracts\Validation\ValidationRule`:

- It delegates to the package rule, resolved through `Rule::turnstile()`.
- It fails with the package's existing messages when the package rule fails.
- It catches only `Illuminate\Http\Client\RequestException` and
  `Illuminate\Http\Client\ConnectionException` from that call. For those, it
  calls `report($e)` so the outage stays visible in the logs, then fails with the
  package's existing `cloudflare-turnstile::errors.internal-error` message. Any
  other exception keeps propagating.

Use it in both call sites in place of `Rule::turnstile()`:
`['bail', 'required', 'string', new Turnstile]`.

This is a root-cause repair in the layer where the defect sits. It is one rule
shared by the two existing call sites, with no new configuration or dependency.

Must not break:
- the production-only gate
- `bail` and `string` handling of non-string tokens (F-02)
- the pass, fail, and missing-token behavior, and the widget markup
- the existing non-production login and registration tests
- vendor code, which stays untouched

## Build steps

- [x] 1. **Outage-safe Turnstile rule.** Add `app/Rules/Turnstile.php` as
  described. Swap it into `LoginRequest::rules()` and
  `RegisteredUserController::store`, and drop the `Rule` import wherever it
  becomes unused. In `tests/Feature/Auth/TurnstileTest.php`, add a dataset test
  for `/login` and `/register` covering a siteverify 500
  (`Http::response([], 500)`) and a connection failure (`Http::failedConnection()`).
  Done when: each case redirects back with a `cf-turnstile-response` session
  error (no 500), the user is neither authenticated nor created, and
  `composer test` passes with all existing Turnstile tests unchanged.
  Notes: the test file now also calls `Http::preventStrayRequests()` in
  `beforeEach`. A first draft of the outage dataset did not register its fakes,
  and the request reached Cloudflare's real endpoint, so this guard keeps any
  Turnstile test from calling Cloudflare. When the package rule fails with no
  error codes, the new rule falls back to the package's `unexpected` message,
  so validation still fails visibly. Reverting only the two call sites made all
  four outage tests fail with `RequestException` or `ConnectionException`,
  which confirms the tests cover F-03.

## Verify

- `composer test` passes, including the new outage cases. Pint passes on the
  changed files and PHPStan passes over `app/`.
- Manual check, optional: locally with `APP_ENV=production` and the always-pass
  test keys, block `challenges.cloudflare.com` from the server, for example with
  a hosts entry pointing at `0.0.0.0`. Submit `/login`; the form shows "An
  internal error happened while validating the response." instead of an error
  page. Revert the hosts entry and `.env` afterwards.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":3785,"specSha256":"882deabcc45c3c12d265b3daa277483ba45e2c50e7e192ff573efc204d25f006","branch":"refs/heads/fix/turnstile-outage-returns-a-500","head":"bbccc9e9a9f75e9ba133778f9a166b88742d506d","baseRef":"refs/heads/main","baseCommit":"ccc4e9d8ee6357fd4510f8af4d50a52850ea7240","sourceTree":"d38a3c19ec91f20a8e52aa9b889d794c0ffe33bf","landing":"local-merge","absentOptional":[]} -->

## Findings

### turnstile-outage-returns-a-500/F-03 [P3] closed - A failed or unreachable siteverify call returns a 500 instead of a validation error

**File:** app/Http/Requests/Auth/LoginRequest.php:37
**Found:** 2026-10-17 by /audit independent (scope: current; lens: quality)
**Why it matters:** The package's `TurnstileClient::siteverify` (`vendor/ryangjchandler/laravel-cloudflare-turnstile/src/TurnstileClient.php:35`) calls `Http::retry(3, 100)`, whose `$throw` argument defaults to `true` (`vendor/laravel/framework/src/Illuminate/Http/Client/PendingRequest.php:659`). After three non-2xx responses it throws `RequestException`, and a network failure throws `ConnectionException`, so the package's `! $response->ok()` fallback is effectively unreachable. With this feature, a Cloudflare siteverify outage or blocked egress in production makes every login and registration POST (`LoginRequest.php:37`, `RegisteredUserController.php:82`) return a 500 rather than a visible `cf-turnstile-response` error. Users are blocked either way during an outage, so this is error handling and log noise, not a bypass. Confirmed by code reading only; no outage was simulated.
**Suggested fix:** Either accept it as a dependency limitation (the spec keeps the package unchanged), or add one test that fakes a 500 siteverify response and, if the 500 is unwanted, map `RequestException`/`ConnectionException` from siteverify to a `cf-turnstile-response` validation error at the two call sites. Nothing current is lost by accepting it.
**Resolution:** Fixed by `/fix` (fix/turnstile-outage-returns-a-500): new `app/Rules/Turnstile.php` wraps the package rule, catches only `RequestException` and `ConnectionException`, calls `report()`, and fails with the package's `internal-error` message. Both call sites use `['bail', 'required', 'string', new Turnstile]`. A dataset test covers a siteverify 500 and a connection failure on `/login` and `/register`. Closed 2026-10-17 by /audit independent current (fresh subagent, claude-opus-5-5) against `ccc4e9d..bbccc9e`: `app/Rules/Turnstile.php:21-27` catches only `RequestException|ConnectionException` thrown by `Http::retry(3, 100)` in the package client, reports it, and fails with the package's `internal-error` message; other exceptions still propagate. Both call sites (`LoginRequest.php:37`, `RegisteredUserController.php:82`) use it behind the unchanged production gate and `bail`/`required`/`string`. The four outage dataset cases in `tests/Feature/Auth/TurnstileTest.php` pass and assert the exact message, no login, and no created user; `composer test` passes (66 tests). No new defect found in the repair.

## Independent review

**Status:** passed
**Target commit:** bbccc9e9a9f75e9ba133778f9a166b88742d506d
**Base commit:** ccc4e9d8ee6357fd4510f8af4d50a52850ea7240
**Base ref:** main
**Spec hash:** 882deabcc45c3c12d265b3daa277483ba45e2c50e7e192ff573efc204d25f006
**Prepared by:** claude
**Builder model:** claude-opus-5-5
**Requested reviewer:** claude
**Requested model:** runtime default (exact model not known until reviewer starts)
**Requested execution:** automatic
**Requested at:** 2026-10-17T19:11:00Z
**Workflow:** regular
**Check required:** no
**Reviewer adapter:** claude
**Reviewer model:** claude-opus-5-5
**Reviewer context:** fresh subagent
**Actual execution:** automatic
**Reviewed at:** 2026-10-17T19:12:37Z
**Scope:** current
**Lenses:** quality, security, performance, tests
**Verdict:** passed
**Check result:** not-required

## Handoff

Review the active spec and the complete `ccc4e9d8ee6357fd4510f8af4d50a52850ea7240..bbccc9e9a9f75e9ba133778f9a166b88742d506d` delta in a fresh
session or isolated subagent without the builder conversation. Run all Audit lenses from scratch.
Run Check when required above. Do not edit product code, accept findings, or
reuse the existing findings as the review scope.

## Commands

- `composer test`: pass (66 tests, 188 assertions; TurnstileTest 16 passed including the 4 outage cases)
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`: pass (no errors)
- `vendor/bin/pint --test` on the 4 changed PHP files: pass

## Evidence

- Freshness verified before writing: `HEAD` = `bbccc9e9a9f75e9ba133778f9a166b88742d506d`; `git merge-base main HEAD` = `ccc4e9d8ee6357fd4510f8af4d50a52850ea7240`; spec SHA-256 matches `882deabc...f006`; spec is tracked and unchanged; only `blueprint/context/review.md` differed from the target.
- Delta reviewed: `app/Rules/Turnstile.php` (new), `app/Http/Requests/Auth/LoginRequest.php:37`, `app/Http/Controllers/Auth/RegisteredUserController.php:82`, `tests/Feature/Auth/TurnstileTest.php`, plus spec and ledger edits.
- Vendor dependencies read: package `Rules/Turnstile.php` (`passes`/`message`), `TurnstileClient::siteverify` (`Http::retry(3, 100)`, throw defaults to true per `PendingRequest.php:659`), service provider macro `Rule::turnstile()` resolves a fresh instance via `app()` (no shared message state), and `lang/en/errors.php` keys used by the rule exist.
- Security: production-only gate, `bail`, `required`, and `string` are unchanged; the rule fails closed on outage (never passes), catches only the two HTTP client exceptions, and `report()` logs exceptions that do not carry the secret.
- Performance: no new network calls; one siteverify call per request as before.
- Tests: outage dataset asserts the exact `internal-error` message, guest state, and no created user; `Http::preventStrayRequests()` prevents real Cloudflare calls; no skipped or focused tests.

## Findings

- None new. F-03 [P3] moved from `fixed` to `closed` after re-review of the repaired code.

## Remaining risk

- The rule's fallback branch for a failed siteverify with an empty `error-codes` array (`app/Rules/Turnstile.php:36-40`) and the `report()` call are not covered by a test; both are simple and low risk.
- A 200 siteverify response with a non-JSON or malformed body would still throw a `TypeError` from the package's `SiteverifyResponse` and return a 500; the spec intentionally lets other exceptions propagate.
- With blocked egress that drops packets, the package's `Http::retry(3, 100)` with the default HTTP client timeout can hold a login or registration request for a long time before the validation error appears; this pre-existing package behavior was not simulated.
- No real outage or browser flow was exercised; Check was not required.
