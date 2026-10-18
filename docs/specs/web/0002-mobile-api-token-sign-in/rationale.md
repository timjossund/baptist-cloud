# 0002. Mobile API and token sign in: rationale

The decision record for [index.md](index.md). `/develop` builds from `index.md` and can skip this file.

## Context

Baptist Cloud is a Laravel 12 website with Breeze sessions, Turnstile on the login and register forms, and no JSON API at all: there is no `routes/api.php` and no token package. The React Native audio app (the `audio-app` scope) needs to sign people in with their existing Baptist Cloud accounts and then call API routes for sermons, search, and following (web features 21 to 23). People sign up and subscribe on the website only, and the app only listens.

A phone app can't use the website's session cookies, CSRF tokens, or Turnstile in a natural way. It needs a credential it can keep in secure storage and send with each request, one that can be taken away if a phone is lost or a password changes. Feature 19 already settled who has full access (`User::hasFullAccess()`), so this feature only has to identify the person and pass that answer along.

Forces: a small team that already runs this Laravel app, a single API client (the audio app) for now, user accounts that contain only light personal data (name, username, avatar, email), and app store review, which favors clear error states over silent failures. Without this, every later mobile feature is blocked, and each would invent its own sign in and error handling.

## Options considered

### Option 1: Laravel Sanctum personal access tokens

Sanctum issues opaque random tokens and stores a SHA-256 hash of each in a `personal_access_tokens` table. `auth:sanctum` checks the bearer token on each request. This adds an idle expiry hook and a JSON error layer on top.

**Pros**:
- First party, small, and documented for exactly this case (mobile app tokens).
- One token per device, revocable one at a time, and the database holds no plain tokens.
- No keys to manage, and no OAuth server to run.

**Cons**:
- Built in expiry counts from creation, not last use, so the 90 day idle rule is custom code.
- Every request writes `last_used_at`.

### Option 2: Laravel Passport (OAuth2 server)

A full OAuth2 server with clients, the password grant or authorization code with PKCE, refresh tokens, and scopes.

**Pros**:
- Standard OAuth flows, ready if other apps or partners ever sign in with Baptist Cloud accounts.
- Short lived access tokens with refresh tokens are built in.

**Cons**:
- Much more to run: encryption keys, client records, refresh logic in the app, and more tables.
- The password grant is discouraged in current OAuth guidance, and the PKCE browser flow adds app work for no gain with one first party client.

### Option 3: JWT package

Signed stateless tokens (a third party package) that the server checks without a database lookup.

**Pros**:
- No token table read per request.

**Cons**:
- Hard to revoke before expiry. Sign out and password changes need a denylist, which brings back the database lookup.
- A third party package and a signing key to rotate, for a performance gain this app doesn't need.

### Option 4: Session cookies through a WebView

Have the app open the website's login page in a WebView and reuse the session cookie.

**Pros**:
- Reuses Turnstile and Breeze as they are.

**Cons**:
- Cookies, CSRF, and session lifetime were built for browsers and break in subtle ways in native HTTP clients. Background audio requests can lose the session.
- A poor sign in experience in a native app, and harder to get through app review.

## Rationale

Sanctum fits the forces exactly: one first party client, a small team, and a need to revoke per device. Passport's extra machinery only pays off with third party clients, and nothing in the scope plans for them. JWT's main benefit (no lookup) is irrelevant at this traffic, and its main cost (hard revocation) collides directly with the sign out, password change, and lost phone requirements. The WebView route keeps browser assumptions in a place where they fail.

The idle expiry (90 days unused) was chosen over a fixed lifetime so daily listeners never get signed out, while a lost and unused phone loses access on its own. Letting free users sign in, and gating content per request with `full.access`, keeps one source of truth (feature 19's rule) and lets the app explain plan state instead of showing a failed sign in. Leaving out Turnstile on the API is a conscious trade: it's built for browsers, and three throttles (per email and IP, per IP, and a per email backstop in case IPs can be faked through the trusted proxy setting) cover guessing at this scale. The stable `{ message, code }` error shape costs a few lines in `bootstrap/app.php` and saves the app from parsing English messages.
