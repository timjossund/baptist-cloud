# Findings

> **Generated file.** The findings ledger: review findings raised by `/audit`
> against the work in progress, each with a durable ID, severity (P0-P3), and
> status. `/implement` marks repaired findings `fixed`, a later `/audit` pass
> moves them to `closed`, and `/complete` refuses to land while any P0 or P1
> finding is `open` or `fixed`, then archives resolved findings with the work
> and resets this file.

### F-03 [P3] open - A failed or unreachable siteverify call returns a 500 instead of a validation error

**File:** app/Http/Requests/Auth/LoginRequest.php:37
**Found:** 2026-10-17 by /audit independent (scope: current; lens: quality)
**Why it matters:** The package's `TurnstileClient::siteverify` (`vendor/ryangjchandler/laravel-cloudflare-turnstile/src/TurnstileClient.php:35`) calls `Http::retry(3, 100)`, whose `$throw` argument defaults to `true` (`vendor/laravel/framework/src/Illuminate/Http/Client/PendingRequest.php:659`). After three non-2xx responses it throws `RequestException`, and a network failure throws `ConnectionException`, so the package's `! $response->ok()` fallback is effectively unreachable. With this feature, a Cloudflare siteverify outage or blocked egress in production makes every login and registration POST (`LoginRequest.php:37`, `RegisteredUserController.php:82`) return a 500 rather than a visible `cf-turnstile-response` error. Users are blocked either way during an outage, so this is error handling and log noise, not a bypass. Confirmed by code reading only; no outage was simulated.
**Suggested fix:** Either accept it as a dependency limitation (the spec keeps the package unchanged), or add one test that fakes a 500 siteverify response and, if the 500 is unwanted, map `RequestException`/`ConnectionException` from siteverify to a `cf-turnstile-response` validation error at the two call sites. Nothing current is lost by accepting it.
**Resolution:**
