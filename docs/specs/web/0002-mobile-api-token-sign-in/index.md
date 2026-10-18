# 0002. Mobile API and token sign in

**Date**: 2026-10-17
**Status**: Accepted

## Summary

This adds the first JSON API to Baptist Cloud, so the phone app can sign in with a Baptist Cloud email and password and get a token (a long random key the app sends with every request). It uses Laravel Sanctum, Laravel's own package for this. A token works until the person signs out on that device, changes their password, or leaves the app unused for 90 days. Free users can sign in too. The API tells the app their plan, and content routes later refuse them with a clear `plan_required` error the app can show.

## Requirements

**User stories**:
- As a listener, I want to sign in on my phone with my Baptist Cloud account so I can use the app without making a new account.
- As a listener, I want to stay signed in while I keep using the app, and sign out on one phone without affecting my other devices.
- As a free user, I want the app to tell me plainly that a plan is needed, instead of failing silently.
- As the site owner, I want sign in guessing slowed down and leaked tokens to stop working, so accounts stay safe.
- As the app developer, I want every error to carry a stable code, so the app can react without reading message text.

**Acceptance criteria**:
- **AC-1**: `POST /api/v1/auth/token` with a correct `email`, `password`, and `device_name` (required, at most 100 characters) for a verified account returns `201` with `token` (the plain token, shown only this once) and `user` (the same payload as AC-5). The token is stored hashed, named after `device_name`. Free users get a token too. Signing in again, even with the same `device_name`, creates another token: names need not be unique, and there is no cap per user.
- **AC-2**: A wrong email or a wrong password returns `422` with code `invalid_credentials`, the message "These credentials do not match our records.", and `errors.email` holding that same message, identical in both cases. No token is created. A wrong email still runs a password hash check, so both cases take about the same time. Missing or invalid fields return `422` with code `validation_failed`, the message "The given data was invalid.", and Laravel's per field `errors`.
- **AC-3**: A correct password on an account whose email is not verified returns `403` with code `email_unverified` and a message telling the person to verify their email from the link they were sent. No token is created. This check runs only after the password is confirmed.
- **AC-4**: Sign in is throttled by three limits, checked after field validation passes (so a badly formed request counts toward none of them):
  - `email_ip`: 5 failed attempts per minute for the same email and IP.
  - `ip`: 20 attempts (failed or not) per minute from one IP across all emails.
  - `email`: 10 failed attempts per 15 minutes for one email from any IP (a backstop in case the client IP can be faked).

  Once any limit is reached, the request returns `429` with code `too_many_attempts`, the message "Too many sign in attempts. Please try again later.", and a `Retry-After` header in seconds (the longest wait if several limits are hit). A blocked request adds no further hits. A wrong email or password is a failed attempt. A correct password on an unverified account is not. A successful sign in clears the `email_ip` and `email` counts, not the `ip` count. Each block logs `Log::warning('API sign in throttled', ['ip' => ..., 'throttle' => 'email_ip'|'ip'|'email'])`, never the email, password, or a token.
- **AC-5**: `GET /api/v1/me` with a valid bearer token returns `200` with `id`, `name`, `username`, `avatar_url` (an absolute URL), `plan` (`admin`, `author`, `member`, or `free`), and `has_full_access` (boolean). `plan` and `has_full_access` come from feature 19's rule (`User::planTier()` and `User::hasFullAccess()`). Email is not included.
- **AC-6**: A missing, malformed, revoked, or idle expired token on any authenticated API route returns `401` with code `unauthenticated` and one generic message. The API never redirects to `/login` and never returns HTML.
- **AC-7**: `DELETE /api/v1/auth/token` revokes only the token used for that request and returns `204`, even when the user is no longer verified. The user's other tokens keep working.
- **AC-8**: A token whose last activity (`last_used_at`, or `created_at` if never used) is strictly more than 90 days ago stops working (AC-6 applies). Every authenticated request updates the token's `last_used_at`. A daily scheduled job deletes those idle tokens.
- **AC-9**: Changing the password from the profile page, resetting it through the email link, or deleting the account deletes all of that user's API tokens.
- **AC-10**: If a token's user is no longer verified (for example after changing their email), authenticated API routes return `403` with code `email_unverified`.
- **AC-11**: A `full.access` route middleware returns `403` with code `plan_required` and a message saying a plan is needed when `User::hasFullAccess()` is false, and lets the request through otherwise. Feature 21 applies it to the sermon routes.
- **AC-12**: Authenticated API routes allow 120 requests per minute per user, shared across all of that user's devices. Over that returns `429` with code `too_many_requests`, the message "Too many requests. Please slow down.", and a `Retry-After` header. Requests refused earlier with `401` or `403` don't count.
- **AC-13**: Every error from a route under `/api/` is JSON shaped `{ "message": string, "code": string }`, with `errors` added on `422`, whatever the `Accept` header says. Codes and fixed messages:
  - `validation_failed` (422): "The given data was invalid."
  - `invalid_credentials` (422): "These credentials do not match our records."
  - `email_unverified` (403): "Please verify your email address using the link we sent you."
  - `unauthenticated` (401): "Please sign in again."
  - `plan_required` (403): "A Member or Author plan is needed for this."
  - `too_many_attempts` (429) and `too_many_requests` (429): the messages in AC-4 and AC-12.
  - `not_found` (404, and 405 which keeps status 405 and its `Allow` header): "Not found."
  - `http_error` (any other 4xx, keeping its status): "The request could not be handled."
  - `server_error` (any 5xx): "Something went wrong on our side."

  No response ever includes a stack trace or exception detail, even with `APP_DEBUG` on. The detail goes to the log only. `Retry-After` and `Allow` headers are kept.
- **AC-14**: The API is bearer token only. API routes start no session, set no cookies, need no CSRF token, and never fall back to the website's session login. Sanctum's cookie (SPA) mode stays off. Laravel's default CORS setting still covers `api/*` with no credentials, which is harmless for bearer tokens and left as is.

## Decision

**Chosen option**: Option 1: Laravel Sanctum personal access tokens, with an idle expiry and a JSON error layer.

Add Sanctum, issue one hashed token per device through `POST /api/v1/auth/token`, check every API request with `auth:sanctum` plus a 90 day idle rule, and render every `/api/` error as `{ message, code }`.

**Implementation skills**: `laravel-best-practices` (`laravel/boost`, `.claude/skills/laravel-best-practices/`)

## Rationale

Reasoning and options: see [rationale.md](rationale.md).

## Feature design

**Data model sketch**:

| Table | Column | Type | Rules |
|---|---|---|---|
| `personal_access_tokens` (new, Sanctum's own migration) | `id` | bigint PK | |
| | `tokenable_type`, `tokenable_id` | morph to `users` | indexed together. Plain class name, no enforced morph map (same as spec 0001) |
| | `name` | string | the `device_name` from sign in, at most 100 characters |
| | `token` | string(64) | SHA-256 hash of the plain token, unique |
| | `abilities` | json, nullable | always `["*"]`. No per route scopes |
| | `last_used_at` | timestamp, nullable | set by Sanctum on each authenticated request |
| | `expires_at` | timestamp, nullable | always null. The idle rule replaces a fixed expiry |
| | `created_at`, `updated_at` | timestamps | |
| `users` (unchanged) | | | uses `HasApiTokens`. 1 user has many tokens (1:N) |

No foreign key (a morph), so tokens are deleted in code when a user is deleted (AC-9).

**State transitions**: token: `active` → `revoked` (deleted on sign out, password change or reset, account deletion) or `idle expired` (rejected once `last_used_at`, or `created_at` if never used, is more than 90 days old, then deleted by the daily job). No other states.

**API surface** (all under `/api/v1`, JSON only, `Accept: application/json` assumed):

| Endpoint | Method | Key inputs | Key outputs | Auth | Key errors |
|---|---|---|---|---|---|
| `/api/v1/auth/token` | POST | `email`: string (req), `password`: string (req), `device_name`: string, max 100 (req) | `201` `{ token, user }` | public, throttled (AC-4) | `422 invalid_credentials`, `422 validation_failed`, `403 email_unverified`, `429 too_many_attempts` |
| `/api/v1/auth/token` | DELETE | none (bearer) | `204` empty | bearer, 120/min (no verified check, AC-7) | `401 unauthenticated` |
| `/api/v1/me` | GET | none (bearer) | `200` `{ id, name, username, avatar_url, plan, has_full_access }` | bearer, verified, 120/min | `401 unauthenticated`, `403 email_unverified`, `429 too_many_requests` |

The user payload is one API Resource (`App\Http\Resources\Api\UserResource`), used by both sign in and `/me`. Responses are not wrapped in a `data` key: sign in returns `{ "token": "...", "user": { ... } }` and `/me` returns the user object directly (call `UserResource::withoutWrapping()` for the API, or return the array).

Middleware stack for authenticated routes: `auth:sanctum`, then `api.verified` (a small middleware returning AC-10's JSON `403`, since Laravel's `verified` middleware redirects or uses a different message), then `throttle:api-user`. `DELETE /auth/token` skips `api.verified`. `full.access` (AC-11) is registered as an alias now and applied by feature 21. The sign in route uses no `throttle:` middleware: its three limits run in the controller with the `RateLimiter` facade, because the middleware would answer with `too_many_requests` instead of `too_many_attempts`.

Error mechanism: one small `App\Exceptions\ApiException` (status, code, message, optional headers and errors) for the codes the app raises itself (`invalid_credentials`, `email_unverified`, `plan_required`, `too_many_attempts`). In `bootstrap/app.php`, `shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson())`, plus `render` closures scoped to `api/*` that turn `ApiException` and Laravel's own exceptions into AC-13's shape:
- `ValidationException` becomes `validation_failed`.
- `AuthenticationException` becomes `unauthenticated`.
- `ThrottleRequestsException` becomes `too_many_requests`.
- `NotFoundHttpException`, `MethodNotAllowedHttpException`, and `ModelNotFoundException` become `not_found`.
- Any other `HttpException` becomes `http_error` or `server_error` by status.
- Any other `Throwable` becomes `server_error`.

Each closure copies the exception's headers.

**Value sourcing**:

| Action | Value produced / displayed | Source |
|---|---|---|
| sign in | user to check | `users.email` matched to the `email` input, password checked with `Hash::check` against `users.password` (via `Auth::getProvider()->retrieveByCredentials` and `validateCredentials`, so no session starts). With no matching user, run `Hash::check` against a fixed dummy hash so timing matches |
| sign in | verified state | `User::hasVerifiedEmail()` (`users.email_verified_at`) |
| sign in | token name | `device_name` input |
| sign in | plain token | `$user->createToken($deviceName)->plainTextToken` (Sanctum, `id|random` form) |
| sign in | `email_ip` throttle key | `'api-login:'.Str::lower(email input).'|'.$request->ip()`, 5 failures per 60 seconds |
| sign in | `ip` throttle key | `'api-login-ip:'.$request->ip()`, 20 attempts per 60 seconds |
| sign in | `email` throttle key | `'api-login-email:'.Str::lower(email input)`, 10 failures per 900 seconds |
| sign in | `Retry-After` | the largest `RateLimiter::availableIn()` among the limits that are blocking |
| sign in, `/me` | `id`, `name`, `username` | `users` columns (`username` is required at registration, so never null) |
| sign in, `/me` | `avatar_url` | `rtrim(config('app.url'), '/').$user->avatar`: the existing `User::avatar` accessor returns a relative path (`/storage/avatars/...` or `/default-avatar.png`). Using `APP_URL` instead of `url()` keeps it stable no matter which host the request came through |
| sign in, `/me` | `plan` | `User::planTier()` (spec 0001) |
| sign in, `/me` | `has_full_access` | `User::hasFullAccess()` (spec 0001) |
| any authenticated route | current token | `$request->user()->currentAccessToken()` |
| idle check | last activity | `personal_access_tokens.last_used_at`, falling back to `created_at` when null |
| idle check | idle check | idle limit | 90 days, one constant shared by the auth hook and the prune job. A token is idle when its last activity is strictly before `now()->subDays(90)` |
| idle check | auth hook result | `Sanctum::authenticateAccessTokensUsing(fn ($token, bool $isValid) => $isValid && ($token->last_used_at ?? $token->created_at)->gte(now()->subDays(90)))`. It must keep `$isValid`, since the hook replaces Sanctum's own check |
| daily prune | tokens to delete | `Schedule::call(...)->daily()` in `routes/console.php`, deleting `PersonalAccessToken::whereRaw('coalesce(last_used_at, created_at) < ?', [now()->subDays(90)])`. Sanctum's `sanctum:prune-expired` ignores `last_used_at`, so it isn't used |
| per user API limit | limiter key | `$request->user()->id`, 120 per minute, limiter name `api-user` |
| `full.access` | allowed or not | `User::hasFullAccess()` |
| error responses | `code` | mapped from the exception type in `bootstrap/app.php` (`withExceptions`), see AC-13 |
| throttle log | IP and which limit | `$request->ip()` and the limit name (`email_ip`, `ip`, or `email`) |
| token revocation | tokens to delete | `$user->tokens()->delete()` in `PasswordController@update`, in a `PasswordReset` event listener, and in a `User::deleting` model hook (covers self deletion and admin deletion) |

**Key invariants**:
- A plain token is shown once, in the sign in response, and is never stored, logged, or returned again.
- Only verified users ever receive a token (AC-3), and a token whose user stops being verified is refused (AC-10).
- A token idle for more than 90 days never authenticates, even before the daily job deletes it.
- Sign out deletes exactly the current token, never others.
- Every response under `/api/` is JSON, success or error. No redirect, no HTML, no session cookie.
- The API login throttle uses its own keys, so it never shares or resets the website's Breeze login counter.
- A user can always revoke their own current token, verified or not.

**Security model**:
- Public: only `POST /api/v1/auth/token`, rate limited per email and IP, per IP, and per email (AC-4). No Turnstile (the website keeps it). The app trusts every proxy (`trustProxies(at: '*')` in `bootstrap/app.php`), so a request that reaches the server directly could fake its IP through `X-Forwarded-For`. The email only limit is the backstop for that, and narrowing the trusted proxies is a Follow-up outside this feature.
- Authenticated: bearer token via `auth:sanctum`, then verified, then 120 per minute per user. Every signed in user, admin or not, gets the same token power (abilities `["*"]`). There are no admin API routes.
- Content access: decided per request by `full.access` from the live subscription, so a lapsed plan is refused on the next call without touching the token.
- Account enumeration: wrong email and wrong password look identical (AC-2). `email_unverified` appears only after a correct password.
- Token storage: hashed with SHA-256 in the database. The app must keep it in the phone's secure storage (decided in audio app feature 1).
- Revocation: sign out, password change or reset, account deletion, and the 90 day idle rule.
- Logging: throttle hits only, with IP. Never log request bodies, passwords, or tokens on these routes.
- No CORS and no cookie mode, so browser pages on other sites cannot call the API with a user's cookies.
- No regulated data beyond name, username, and avatar. Email stays out of the API.

**Configuration required**:
- No new environment variables. In `config/sanctum.php`, set `'guard' => []` (so Sanctum never falls back to the website session), keep `expiration` at `null` (the idle rule handles expiry), and leave `stateful` unused. Don't call `statefulApi()`.
- Production runs `php artisan migrate` for the new table. The scheduler (`schedule:run` every minute) is already required by spec 0001 and also runs the daily token prune.

**Critical test scenarios**:
- Happy path: a verified free user signs in with a device name, gets a token and `plan: free`, calls `/me` with it, signs out, and the same token then gets `401 unauthenticated`. Verifies **AC-1**, **AC-5**, **AC-6**, **AC-7**.
- Two devices: sign in twice, sign out with the first token, the second token still works. Verifies **AC-7**.
- Wrong email and wrong password both return `422 invalid_credentials` with identical bodies and no token rows. A missing `device_name` returns `422 validation_failed`. Verifies **AC-2**.
- A user whose `email_verified_at` was cleared can still sign out (`204`). Verifies **AC-7**.
- Unverified account with the correct password gets `403 email_unverified`, with a wrong password still `422`. Verifies **AC-3**.
- Throttles: the 6th failed attempt in a minute for one email and IP gets `429 too_many_attempts` with `Retry-After`, and a log warning names `email_ip`. The 21st attempt from one IP across different emails names `ip`. The 11th failure for one email spread across IPs within 15 minutes names `email`. A correct password on an unverified account adds no failure, and a success clears the `email_ip` and `email` counts. Verifies **AC-4**.
- Idle: travel 91 days past `last_used_at`, the token gets `401`. At 89 days it still works and `last_used_at` moves forward. The prune deletes only the idle one. Verifies **AC-8**.
- Password update, password reset, and account deletion each leave the user with zero tokens. Verifies **AC-9**.
- A verified user with a token whose `email_verified_at` is then cleared gets `403 email_unverified` on `/me`. Verifies **AC-10**.
- `full.access` on a test route: free user `403 plan_required`, Member and admin pass. Verifies **AC-11**.
- 121 requests in a minute on `/me` get `429 too_many_requests`. Verifies **AC-12**.
- `/api/v1/nope` returns `404 not_found` JSON, a forced exception returns `500 server_error` with no trace, and `/me` with no token returns JSON `401`, not a redirect, even without an `Accept` header. Verifies **AC-6**, **AC-13**.
- Sign in sets no `Set-Cookie` header and needs no CSRF token, and a logged in website session never authenticates an API call without a bearer token. Verifies **AC-14**.
- Test setup: use real `createToken()` tokens (not `Sanctum::actingAs`, whose temporary token makes sign out and `last_used_at` checks meaningless), and call `$this->app['auth']->forgetGuards()` between requests in one test. Sanctum caches the user per process, so sign out, idle, and unverified tests could otherwise pass falsely.

## Build plan

Tracer Bullet: first get one real thread working end to end (install, sign in, `/me`, sign out, JSON errors), then thicken it with the safety rules (throttles, idle expiry, revocation, gates).

1. [x] Install Sanctum with `php artisan install:api` (adds `routes/api.php` under the `/api` prefix, `config/sanctum.php`, and the `personal_access_tokens` migration), confirm `HasApiTokens` is on `User`, set the Sanctum guard list to empty, and run the migration. Group routes under the `v1` prefix in `routes/api.php`. Satisfies **AC-1**, **AC-14**
2. [x] JSON error layer: `App\Exceptions\ApiException` plus `shouldRenderJsonWhen` and `render` closures in `bootstrap/app.php`, mapping every exception to AC-13's codes and messages, keeping `Retry-After` and `Allow`, and returning `401 unauthenticated` instead of a redirect. Satisfies **AC-6**, **AC-13**, **AC-14**
3. [x] `POST /api/v1/auth/token` (an `Api\TokenController@store` with a form request for the three fields): check credentials without a session (with the dummy hash on an unknown email), refuse unverified users after the password check, create the token named after the device, and return `{ token, user }` with `UserResource`. Satisfies **AC-1**, **AC-2**, **AC-3**
4. [x] `GET /api/v1/me` returning `UserResource` (absolute `avatar_url`, `plan`, `has_full_access`) behind `auth:sanctum` and the `api.verified` middleware. Satisfies **AC-5**, **AC-10**
5. [x] `DELETE /api/v1/auth/token` (no verified check) deleting `currentAccessToken()`, returning `204`. Satisfies **AC-7**
6. [x] Throttles: the three sign in limits in the controller with the `RateLimiter` facade (validate first, no hits once blocked, the longest `Retry-After`, a log warning per block, and clearing `email_ip` and `email` on success), and the `api-user` limiter at 120 per minute for authenticated routes. Satisfies **AC-4**, **AC-12**
7. [x] Idle expiry: register `Sanctum::authenticateAccessTokensUsing` in `AppServiceProvider` (keeping `$isValid`), and add the daily prune closure in `routes/console.php`. Satisfies **AC-8**
8. [x] Revocation: `$user->tokens()->delete()` in `PasswordController@update`, in a `PasswordReset` listener, and in a `User::deleting` hook. Satisfies **AC-9**
9. [x] `full.access` middleware alias returning `403 plan_required`, ready for feature 21. Satisfies **AC-11**
10. [x] Pest feature tests for the critical scenarios above (`tests/Feature/Api/`), tagged with the AC each covers. Satisfies **AC-1** to **AC-14**

## Consequences

**Positive**:
- One small, first party package, no new service to run. Tokens can be revoked one at a time, which JWTs can't do.
- The app gets a stable error contract and the user's plan state from day one, so the plan needed screen and sign in errors are simple to build.
- Features 21 to 23 reuse the same prefix, middleware, error layer, and `UserResource` patterns.

**Negative / tradeoffs**:
- Every authenticated request writes `last_used_at` (one small update per request). Fine at this scale. Revisit only if the API gets heavy traffic.
- The idle rule is custom code around Sanctum, so it has to be kept in step with Sanctum upgrades.
- No Turnstile on the API, so the throttles are the only brute force defense there.
- Free users can sign in and hold tokens without being able to play anything, so token rows exist for people who never pay.
- Sign in through the API can't use the website's "remember me" or the password confirmation pages. People reset passwords on the website.

**Neutral**:
- New `routes/api.php` and `api` route group, a first for this project.
- The app must store the token in secure storage and send `Authorization: Bearer <token>` with every call.

## Follow-up

- [ ] Connect the Laravel Boost MCP server for Claude Code (it's already installed, but `boost.json` only lists other editors). Run `claude mcp add -s project laravel-boost -- php artisan boost:mcp` from the project root. Note that `php artisan boost:install` can rewrite guideline files.
- [ ] Record the new `laravel-best-practices` skill (`.claude/skills/laravel-best-practices/`) in the `## Agent skills` section of root `AGENTS.md` (it affects every PHP file, so root is the right home), plus the Boost MCP line once connected. `/sync` owns that edit.
- [ ] Decide whether `skills-lock.json` (created by the skill install) is committed.
- [ ] Feature 21 (Sermon API): apply `full.access` to its routes and reuse the error codes and `v1` prefix.
- [ ] Narrow `trustProxies(at: '*')` in `bootstrap/app.php` to Cloudflare's ranges (or read `CF-Connecting-IP`), so client IPs can't be faked through `X-Forwarded-For`. This affects the website's login throttle too, so it's worth its own `/debug` or fix.
- [ ] Audio app feature 1: store the token in the phone's secure storage, and treat any `401` as "sign in again".
- [ ] If the app ever lets people create accounts, Apple requires account deletion inside the app. Today accounts are made on the website, so this doesn't apply yet.
