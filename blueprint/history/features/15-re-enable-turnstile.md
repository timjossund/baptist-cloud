# Feature: Re-enable Turnstile

**From build-plan:** feature 15
**Build attempt:** 1
**Branch:** feature/re-enable-turnstile
**Status:** verified

## Goal

Put Cloudflare Turnstile back on the login and registration forms so bots
cannot submit them, and make it work when Livewire navigation loads those pages.
The check runs in production only, as before. Real users who pass the challenge
see no change, and a failed or missing challenge shows a visible error instead
of failing silently.

## Root cause of the earlier removal

The user reported that Livewire loaded the page but Turnstile never contacted
Cloudflare. The code supports this explanation:

- `@turnstileScripts()` loads `api.js` once, in `<head>`, using implicit
  rendering. Implicit rendering scans the page for `.cf-turnstile` elements only
  on a full page load.
- Guests mostly reach `/login` through `wire:navigate` links. For example,
  `components/post-item.blade.php` links to the single-post route, which needs
  `auth`. Livewire follows the redirect to `/login` and swaps the body in
  without a reload, and nothing tells Turnstile to render the new widget. The
  form then posts with no `cf-turnstile-response`, so validation fails every time.
- The navigation-bar Login and Register links are plain links, so those paths
  did a full reload and could appear to work some of the time.

The fix is to render widgets explicitly, both when the script loads and on every
`livewire:navigated` event.

## In scope

- Swap `@turnstileScripts()` for an explicit-render setup that also renders
  widgets after Livewire navigation.
- Show the `<x-turnstile />` widget on `auth/login` and `auth/register` in
  production.
- Validate `cf-turnstile-response` with `Rule::turnstile()` on login and
  registration requests in production, before the credentials are checked or a
  user is created.
- Show Turnstile validation errors on both forms with `<x-input-error>`.
- Repairs for review findings F-01 (Back-button restore) and F-02 (non-string token).
- Pest feature tests covering the production pass, fail, and missing-token cases
  and the widget markup, plus the existing non-production behavior.

## Out of scope

- Turnstile on other forms (password reset, post reporting, listings, profile).
- Changing the Turnstile package, keys, or `config/services.php`.
- Removing `wire:navigate` from links, or changing which routes need auth.
- Any other edits to the login or registration flow (avatar handling, the
  commented-out legacy `store` method).
- Running the check outside production.

## Build loop

- `workflow.stepReview` is `feature`: implement all steps, then present one
  feature-level review packet.
- `workflow.checkpointCommits` is `disabled`: no step commits. `/complete`
  creates the final feature commit.
- Run `composer test` after each step; it must stay green.

## Build steps

- [x] 1. **Server-side validation in production.** Add the production-only
  `cf-turnstile-response` rule (`['required', Rule::turnstile()]`) to
  `LoginRequest::rules()` and to the existing `$request->validate([...])` in
  `RegisteredUserController::store`. Remove the commented-out Turnstile blocks
  from both controllers and any `use` statements left unused.
  Done when: outside production, the existing login and registration tests
  still pass unchanged; in production, a request without a token is rejected
  with a `cf-turnstile-response` error and no session or user is created.
- [x] 2. **Navigation-safe script loading.** In `layouts/app.blade.php`, replace
  `@turnstileScripts()` with a production-only (`@production`) block containing:
  - a small inline script that defines a global render function. It calls
    `turnstile.render(el)` for each `.cf-turnstile` element without a rendered
    widget (an element with no child nodes), and returns early if `window.turnstile`
    is not loaded yet
  - a listener that calls the render function on `livewire:navigated`
  - `<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=<fn>" async defer></script>`,
    so the first full load renders through the onload callback
  Done when: outside production, the layout outputs no Turnstile script; in
  production, it outputs the explicit-render script tag and the
  `livewire:navigated` listener exactly once.
- [x] 3. **Widget and error display.** In `login.blade.php` and
  `register.blade.php`, replace `{{-- <x-turnstile /> --}}` with `<x-turnstile />`
  inside `@production` / `@endproduction`, followed by
  `<x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />`.
  Done when: `/login` and `/register` render without the widget outside
  production, and render the `cf-turnstile` div with the configured site key in
  production.
- [x] 4. **Tests.** Add Pest feature tests that switch the app environment to
  production and use `Http::fake()` for
  `https://challenges.cloudflare.com/turnstile/v0/siteverify`:
  - login and registration succeed when siteverify returns `success: true`
  - both fail with a `cf-turnstile-response` error, and the user is neither
    authenticated nor created, when siteverify returns `success: false`
  - both fail without a token, and no siteverify request is sent
  - both pages render the widget div and the explicit-render script in production
  Done when: `composer test` passes with the new tests and the existing suite.
- [x] 5. **Browser check of the navigation path.** Locally, set the app
  environment to production and use Cloudflare's always-pass test keys in `.env`.
  As a guest, click a post link on `/` (it uses `wire:navigate` and redirects
  to `/login`), then confirm the widget renders and the login succeeds.
  Repeat with a full page load of `/register`. Restore `.env` afterwards.
  Done when: the widget renders and the form submits successfully on both the
  Livewire-navigated path and the full-load path. Record whatever evidence was
  actually observed.
  Evidence (2026-10-17, built-in browser, `APP_ENV=production`, always-pass test
  keys): as a guest on `/`, clicking the "Hike" post link redirected to `/login`
  within the same document (a window marker survived, so no full reload). The
  widget rendered and filled `cf-turnstile-response` with
  `XXXX.DUMMY.TOKEN.XXXX`. Submitting deliberately wrong credentials returned only
  "These credentials do not match our records.", with no Turnstile error, so
  the token passed live siteverify. A full load of `/register` rendered exactly
  one widget with the token. Submitting with mismatched passwords returned only
  the password-confirmation error, and no user was created. A successful sign-in
  or account creation was not attempted, to avoid real credentials and test
  accounts; the Pest tests cover those success paths.
- [x] 6. **Repair F-01: re-render after Back-button restores.** In the layout
  script, keep a `WeakSet` of the `.cf-turnstile` elements that were passed to
  `turnstile.render`, and stop relying on the element being empty. For any
  element not in the set, call `el.replaceChildren()` to drop leftover widget
  markup and the stale `cf-turnstile-response` input, then render it and add it
  to the set. A `WeakSet` cannot survive Livewire's HTML snapshot, so restored
  elements always count as new.
  Done when: as a guest, the path post link, then `/login`, then the
  `wire:navigate` logo, then Back shows a live widget with one fresh
  `cf-turnstile-response` input, and the first-load and navigated paths still
  render exactly one widget. Checked in the browser with the always-pass keys.
  Evidence (2026-10-17, built-in browser, `APP_ENV=production`, always-pass test
  keys). Before the repair, the Back step left no visible widget, with a stale
  input restored from the snapshot. After the repair: a full load of `/register`
  gave 1 widget and 1 input. Post link to `/login` stayed in the same document
  and gave 1 widget and 1 input. Logo then Back to `/login` showed a live
  "Success!" widget (69px high), 1 widget and 1 input. The test keys always
  return the same dummy token, so freshness is shown by the re-render, not by a
  new token value.
- [x] 7. **Repair F-02: reject non-string tokens.** Change both rules to
  `['bail', 'required', 'string', Rule::turnstile()]` in `LoginRequest::rules()` and
  `RegisteredUserController::store`. Add a test that posts
  `cf-turnstile-response` as an array to each form. `bail` is required:
  without it, Laravel still runs the Turnstile rule after `string` fails, and the
  array reaches the package's `siteverify(string)`.
  Done when: both array posts return a `cf-turnstile-response` validation error
  (no 500), no siteverify request is sent, and `composer test` passes.

## Files / areas

- `app/Http/Requests/Auth/LoginRequest.php` - login rule
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` - remove dead block and unused import
- `app/Http/Controllers/Auth/RegisteredUserController.php` - registration rule, remove dead block
- `resources/views/layouts/app.blade.php` - explicit-render script and navigation hook
- `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php` - widget and error
- `tests/Feature/Auth/` - new Turnstile tests (a new `TurnstileTest.php`, or added to the existing auth tests)
- Read-only: `config/services.php` (`services.turnstile.key` / `secret`),
  `vendor/ryangjchandler/laravel-cloudflare-turnstile`

## Data / contracts

- Form field: `cf-turnstile-response`, the hidden input the widget adds inside the form.
- Server check: the package's `TurnstileClient::siteverify` POSTs to the Cloudflare
  siteverify endpoint with `services.turnstile.secret`. A non-200 response or
  `success: false` fails validation with the package's translated error messages.
- Environment gate: `app()->environment('production')` on the server and
  `@production` in Blade, for the script, the widget, and the rule. All three
  always switch on and off together.
- The widget reads its site key from the `data-sitekey` attribute the package
  component outputs, so `turnstile.render(el)` needs no extra options.
- Env vars (already present): `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`.
  Production must have real keys set, or every login and registration will fail.
- No database or stored-data changes.

## Testing

- Test command: `composer test` (Pest). Logic gate applies.
- Switch environments in tests with `$this->app->detectEnvironment(fn () => 'production')`
  or an equivalent repository-native approach. Fake HTTP; never call Cloudflare.
- Set the `services.turnstile` config in tests to Cloudflare's published test keys
  (`TurnstileClient::SITEKEY_ALWAYS_PASSES_VISIBLE`, `SECRET_KEY_ALWAYS_PASSES`)
  so the widget renders a known site key.
- No browser test harness exists, so the client-side render after
  `livewire:navigated` is covered only by the manual check in step 5. Live
  Cloudflare verification is not tested.

## Notes for the AI

- Login validation belongs in `LoginRequest::rules()`, so the token is checked
  before `authenticate()` runs and a bot never reaches the rate-limited
  credential check.
- In registration, merge the rule into the existing validate array. It must run
  before the avatar is processed and before `User::create`.
- Login and register pages use `<x-app-layout>`. Keep the Turnstile script
  in the layout `<head>` only, never inside the auth views, so that Livewire
  navigation does not load it twice.
- Livewire fires `livewire:navigated` on the first page load too. The guard
  (`window.turnstile` loaded, element empty) must make repeated calls safe so a
  widget is never rendered twice.
- Follow `coding-standards.md`: no commented-out code, no em dashes, minimal comments.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":11761,"specSha256":"d1d3edd0440ce05c691056bcd4c0b023bfe28c3a352c214eeec4d16936552406","branch":"refs/heads/feature/re-enable-turnstile","head":"f176854b7388a71105e7eafb73dfa36fc321a886","baseRef":"refs/heads/main","baseCommit":"f1641bd86d6c48931182a1b27e2ad81ec84bdcda","sourceTree":"21c7636f7065d50a51b50ba0f476449ff684df8f","landing":"local-merge","absentOptional":[]} -->

## Findings

### 15/F-01 [P2] closed - Back-button restore of a Livewire page skips re-rendering the Turnstile widget

**File:** resources/views/layouts/app.blade.php:26
**Found:** 2026-10-17 by /audit independent (scope: current; lens: quality)
**Why it matters:** `renderTurnstileWidgets` renders only `.cf-turnstile` elements with no child nodes. Livewire 4 (`vendor/livewire/livewire/dist/livewire.esm.js:11916-11922`) stores `document.documentElement.outerHTML` before a `wire:navigate` visit, restores that HTML on Back, then fires `livewire:navigated`. A guest on `/login` who clicks the `wire:navigate` logo or Home link (`resources/views/layouts/navigation.blade.php:8`) and then presses Back would get a restored container that already holds the old widget's serialized children (Turnstile's light-DOM hidden input and host element). The guard then skips `turnstile.render`, so no live widget appears and submitting likely fails with a visible `cf-turnstile-response` error until a full reload. Not reproduced in a browser; this pass could not start servers.
**Suggested fix:** Track rendered containers in memory instead of relying on emptiness, for example a `WeakSet` of elements passed to `turnstile.render(el)` (it does not survive HTML serialization). On `livewire:navigated`, clear and re-render any `.cf-turnstile` not in the set (`el.replaceChildren()` before `render`). Confirm with a manual Back-button check.
**Resolution:** Reproduced in the browser, then fixed in spec step 6: the layout tracks rendered containers in a `WeakSet` and clears and re-renders any container not in it. A browser re-check of post link, `/login`, logo, then Back showed a live widget with one input. Closed 2026-10-17 by /audit independent (fresh subagent) at f176854: `resources/views/layouts/app.blade.php:22-40` now keys rendering on an in-memory `WeakSet` and calls `el.replaceChildren()` before `turnstile.render(el)`, so a container restored from Livewire's `outerHTML` snapshot is re-rendered. The inline head script is not re-executed on navigation (Livewire skips head scripts whose hashed `outerHTML` already exists, `livewire.esm.js:12342`), so the set and listener persist and repeated `livewire:navigated` calls stay idempotent. No new defect found in the repair.

### 15/F-02 [P3] closed - A non-string cf-turnstile-response causes a 500 instead of a validation error

**File:** app/Http/Requests/Auth/LoginRequest.php:37
**Found:** 2026-10-17 by /audit independent (scope: current; lens: security)
**Why it matters:** The rule is `['required', Rule::turnstile()]` in both `LoginRequest::rules()` and `RegisteredUserController::store` (`app/Http/Controllers/Auth/RegisteredUserController.php:82`). A client posting `cf-turnstile-response[]=x` passes `required`, then Laravel's `Validator::validateUsingCustomRule` hands the array to `Turnstile::passes`, which calls `TurnstileClient::siteverify(string $response)` and throws a `TypeError`. The request is still rejected, so this is not a bypass, but untrusted input produces a 500 and error-log noise instead of a validation error.
**Suggested fix:** Add `'string'` before the Turnstile rule in both places (`['required', 'string', Rule::turnstile()]`) and optionally one test that posts an array value and expects a `cf-turnstile-response` session error.
**Resolution:** Fixed in spec step 7 with `['bail', 'required', 'string', Rule::turnstile()]` in both places (`bail` is needed because Laravel otherwise still runs the Turnstile rule). A new dataset test posts an array to both forms and expects a validation error with no siteverify call. Closed 2026-10-17 by /audit independent (fresh subagent) at f176854: both `LoginRequest.php:37` and `RegisteredUserController.php:82` use `['bail', 'required', 'string', Rule::turnstile()]`, and the dataset test in `tests/Feature/Auth/TurnstileTest.php:139-152` passes under `composer test` with `Http::assertNothingSent()`. No new defect found in the repair.

## Independent review

**Status:** passed
**Target commit:** f176854b7388a71105e7eafb73dfa36fc321a886
**Base commit:** f1641bd86d6c48931182a1b27e2ad81ec84bdcda
**Base ref:** main
**Spec hash:** d1d3edd0440ce05c691056bcd4c0b023bfe28c3a352c214eeec4d16936552406
**Prepared by:** claude
**Builder model:** claude-opus-5-5
**Requested reviewer:** claude
**Requested model:** runtime default (exact model not known until reviewer starts)
**Requested execution:** automatic
**Requested at:** 2026-10-17T18:41:40Z
**Workflow:** regular
**Check required:** no
**Reviewer adapter:** claude
**Reviewer model:** claude-opus-5-5
**Reviewer context:** fresh subagent
**Actual execution:** automatic
**Reviewed at:** 2026-10-17T18:43:55Z
**Scope:** current
**Lenses:** quality, security, performance, tests
**Verdict:** passed
**Check result:** not-required

## Handoff

Review the active spec and the complete `f1641bd86d6c48931182a1b27e2ad81ec84bdcda..f176854b7388a71105e7eafb73dfa36fc321a886` delta in a fresh
session or isolated subagent without the builder conversation. Run all Audit lenses from scratch.
Run Check when required above. Do not edit product code, accept findings, or
reuse the existing findings as the review scope.

## Commands

- `composer test`: pass (62 tests, 172 assertions; all 12 `Tests\Feature\Auth\TurnstileTest` cases pass)
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` on the three changed app PHP files: pass (no errors). Run against the Pest test file it reports Pest-dynamic `$this` method errors; the project config analyses `app/` only and existing auth tests produce the same noise
- `vendor/bin/pint --test` on the four changed PHP files: pass
- Freshness checks (`git rev-parse HEAD`, `git merge-base main HEAD`, `shasum -a 256` on the tracked spec, `git status --porcelain`): pass; HEAD, base, and spec hash match the request and only `blueprint/context/review.md` differed before this pass

## Evidence

- Reviewed the full `f1641bd..f176854` delta: `LoginRequest.php`, `RegisteredUserController.php`, `AuthenticatedSessionController.php`, `layouts/app.blade.php`, `auth/login.blade.php`, `auth/register.blade.php`, `tests/Feature/Auth/TurnstileTest.php`, plus the spec
- Rule runs in `LoginRequest::rules()` before `authenticate()` and in the registration validate array before avatar handling and `User::create`; server rule, widget, and script are all gated on production together
- Package component outputs `class="cf-turnstile"` with `data-sitekey`, so `turnstile.render(el)` needs no options; the inline render function is defined before the async `api.js` onload callback
- Livewire skips head scripts already present by hashed `outerHTML` (`livewire.esm.js:12342`), so the render closure and `livewire:navigated` listener are registered once
- No other `<x-turnstile>` consumers relied on the removed `@turnstileScripts()`; no CSP middleware blocks the inline or Cloudflare script
- F-01 and F-02 repairs re-examined and closed

## Findings

- F-01 [P2] closed
- F-02 [P3] closed
- F-03 [P3] open (non-blocking): siteverify outage returns a 500 instead of a validation error

## Remaining risk

- The client-side render after `livewire:navigated` and Back restore has no automated test (no browser harness); it relies on the builder's recorded manual browser evidence, which this reviewer did not re-run
- Live Cloudflare siteverify behavior and real production keys were not exercised; tests fake HTTP
- No outage simulation for F-03

## Try it now

Prerequisite: in `.env`, `APP_ENV=production` with Cloudflare's always-pass test keys (`TURNSTILE_SITE_KEY=1x00000000000000000000AA`, `TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA`).

1. In a private window, open http://baptist-cloud.test/ and click any post title. You land on `/login` (via `wire:navigate`) with a Cloudflare widget showing "Success!".
2. Click the site logo, then press Back. The widget renders again on `/login`.
3. Open http://baptist-cloud.test/register directly. Exactly one widget appears.
4. Optional: set `TURNSTILE_SECRET_KEY=2x0000000000000000000000000000000AA` and submit the login form. A Turnstile error appears under the widget.
5. Restore the real keys and the original `APP_ENV` afterwards.
