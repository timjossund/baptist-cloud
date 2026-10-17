# Fix: Patch composer audit advisories

**Type:** Fix
**Status:** verified
**Branch:** fix/patch-composer-audit-advisories

## The problem

`composer audit` reports 3 advisories in 2 locked packages:

| Package | Installed | Advisory | Severity |
| --- | --- | --- | --- |
| league/commonmark | 2.10.1 | GHSA-3q6v-r5mr-hxv8: quadratic-time DoS in the GFM table extension block-start scan | high |
| league/commonmark | 2.10.1 | GHSA-97jj-33gv-5xf9: DisallowedRawHtml bypass when a disallowed tag name ends the raw-HTML literal | medium |
| symfony/http-foundation | v7.4.18 | CVE-2026-108082: ReDoS in `HeaderUtils::split()` via the `Cache-Control` request header | unrated |

The commonmark issues are reachable from user input: posts and listings render
user-written Markdown through `App\Support\Markdown` and `Str::markdown`. The
http-foundation issue is reachable from any request header.

## The fix

Update the two packages within their existing constraints:
`composer update league/commonmark symfony/http-foundation --with-dependencies`.
No new packages and no `composer.json` constraint changes. Only `composer.lock`
changes. Markdown escaping behavior must stay the same.

## Build steps

1. Run the targeted update. Done when `composer.lock` locks league/commonmark
   above 2.10.1 and symfony/http-foundation at v7.4.19 or later, and
   `composer audit` reports no advisories.

## Verify

- `composer audit` reports no advisories.
- `composer test` passes, including the Markdown escaping tests.
- `vendor/bin/phpstan analyse` and `vendor/bin/pint --test` are clean.

## Result

- Locked: league/commonmark 2.10.1 -> 2.10.4, symfony/http-foundation v7.4.18 -> v7.4.21,
  plus symfony/polyfill-mbstring, polyfill-php80, polyfill-php83 -> v1.43.0 (pulled in by
  `--with-dependencies`). No `composer.json` changes.
- `composer audit`: no security vulnerability advisories found.
- `composer test`: 87 passed, including `post body html is escaped when rendered` and
  `listing html is escaped when rendered`.
- `vendor/bin/phpstan analyse`: no errors. `vendor/bin/pint --test`: passed.
- Environment note: in a fresh checkout, `PostAccessTest` (delete) needs
  `IONOS_DEFAULT_REGION` set because it does not fake the `postImages` disk. This is
  pre-existing and unrelated to this fix.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":2296,"specSha256":"6242f597c6888d7e80d950fa424fbb26171320fd76ba852f26e0819489cc71c2","branch":"refs/heads/fix/patch-composer-audit-advisories","head":"1f5f53cbe935ccd167e45f375aa3ca30ddb03f89","baseRef":"refs/heads/main","baseCommit":"1322ed731d9796d2d0acf6be91c5f10325818aa3","sourceTree":"081fc973c4b7de718e6eb0504397a72943338691","landing":"local-merge","absentOptional":[]} -->

## Try it now

Run or do:

```bash
composer audit
```

Expected:

`No security vulnerability advisories found.`

This proves:

The locked league/commonmark and symfony/http-foundation versions no longer carry the three reported advisories.
