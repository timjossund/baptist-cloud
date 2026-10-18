# Verify: Mobile API and token sign in · spec 0002 · updated 2026-10-17
_Steps derived from spec 0002 acceptance criteria. `/check verify` runs these; `/test` locks the durable ones._

Base URL: `http://baptist-cloud.test`. These are API calls, so a terminal with `curl` works well. Use a verified test account from your local database.

## Manual (curl against the running app)
- [x] `POST /api/v1/auth/token` with a verified account's `email`, `password`, and `device_name: "iPhone"` → `201` with `token` and a `user` object (`id`, `name`, `username`, `avatar_url`, `plan`, `has_full_access`, no `email`); a `personal_access_tokens` row named `iPhone` holds a 64 character hash, not the plain token → AC-1, AC-5
- [x] Sign in again with the same `device_name` → a second token row, same name → AC-1
- [x] Wrong email, then wrong password → both `422`, identical bodies with `code: invalid_credentials` and `errors.email`; no new token rows → AC-2
- [x] Leave out `device_name`, or send 101 characters → `422 validation_failed` with `errors.device_name` → AC-2
- [x] Clear `email_verified_at` on the test account and sign in with the right password → `403 email_unverified`; wrong password still `422` → AC-3
- [x] Six wrong passwords in a minute for one email → the sixth gets `429 too_many_attempts` with a `Retry-After` header; `storage/logs/laravel.log` has `API sign in throttled` with `throttle: email_ip` and no email → AC-4
- [x] `GET /api/v1/me` with `Authorization: Bearer <token>` → `200`, same user object as sign in → AC-5
- [x] For a free account `plan` is `free` and `has_full_access` is `false`; for a Member, Author, or admin it matches the admin table label and `has_full_access` is `true` → AC-5 (plan source: `User::planTier()`)
- [x] `avatar_url` starts with `APP_URL` for both an uploaded avatar and the default avatar → AC-5 (avatar source: `APP_URL` plus `User::avatar`)
- [x] `GET /api/v1/me` with no token, and with a made up token, and with no `Accept` header → JSON `401 unauthenticated`, never a redirect to `/login` → AC-6, AC-13
- [x] `DELETE /api/v1/auth/token` with token A → `204`; token A then gets `401`; token B from the other sign in still gets `200` → AC-7
- [x] In tinker, set a token's `last_used_at` to 91 days ago → it gets `401`; set another to 89 days ago → it gets `200` and `last_used_at` moves to now → AC-8 (idle source: `last_used_at`, else `created_at`)
- [x] Run `php artisan schedule:test --name=prune-idle-api-tokens` → only tokens idle more than 90 days are deleted → AC-8
- [x] Change the password on `/profile` → all that user's tokens are gone; same after a reset through the email link; same after deleting the account → AC-9
- [x] With a valid token, clear `email_verified_at` → `/me` gets `403 email_unverified`, but `DELETE /api/v1/auth/token` still gets `204` → AC-7, AC-10
- [x] 121 calls to `/me` within a minute with one user's tokens (spread across two tokens) → the 121st gets `429 too_many_requests` with `Retry-After` → AC-12
- [x] `GET /api/v1/nope` → `404 not_found` JSON; `PUT /api/v1/me` → `405` with an `Allow` header and `code: not_found` → AC-13
- [x] Sign in response has no `Set-Cookie` header; signed in to the website in a browser, `/api/v1/me` without a bearer token still gets `401` → AC-14

## Commands
- [x] `php artisan test tests/Feature/Api` → 23 tests pass → AC-1 to AC-14
- [x] `php artisan route:list --path=api/v1` → exactly the three routes: `POST auth/token`, `DELETE auth/token`, `GET me` → AC-1, AC-5, AC-7
- [x] `php artisan db:table personal_access_tokens` → table exists with `token varchar(64)`, `last_used_at`, `expires_at` → AC-1, AC-8
- [x] `vendor/bin/phpstan analyse` and `vendor/bin/pint --test` → clean

## Acceptance criteria coverage
- AC-1: sign in, second sign in, test suite · AC-2: wrong credentials, validation · AC-3: unverified sign in · AC-4: throttle step · AC-5: `/me`, plan, avatar · AC-6: missing token · AC-7: sign out, unverified sign out · AC-8: idle and prune · AC-9: password change, reset, deletion · AC-10: unverified token · AC-11: test suite (`full.access` has no real route until feature 21) · AC-12: 121 calls · AC-13: 404, 405, missing token · AC-14: cookies and website session
