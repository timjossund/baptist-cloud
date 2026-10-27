# Fix: Admin table test regex hits PCRE JIT limit

**Type:** Fix
**Status:** verified
**Branch:** fix/admin-table-test-regex-hits-pcre-jit-limit

## The problem

`tests/Feature/MeteredAccessTest.php`, test `the admin table labels admin,
author, member, and free users`, checks each user's label with
`toMatch('/'.$name.'((?!<\/tr>).)*>\s*'.$label.'\s*</s')` against the whole
`/admin` page. "Site Admin" first appears in the nav dropdown, so the
tempered-dot group walks about 250 KB of HTML one character at a time. Under
Flock's PHP 8.5 (PCRE JIT on), `preg_match` returns `false` with "JIT stack
limit exhausted" and the test fails. The other four names match. The app is
fine; Herd's PHP config happened to tolerate the pattern.

## The fix

Make the assertion scoped and cheap. Split the page into table rows with
`preg_match_all('/<tr\b.*?<\/tr>/s', ...)`, find the one row that contains
each user's name, and assert that row has a cell whose text is exactly the
label (`/>\s*Label\s*</`). This keeps what the test proves: each user's row
in the admin table shows the right label.

Must not break: any other test, and production code, which stays unchanged.

## Build steps

- [x] 1. **Row-scoped label assertion.** Replace the loop body in that test as
  described.
  Done when: the test passes under Flock's PHP, reverting a label in the row
  check (e.g. expecting `Free` for "Member Reader") makes it fail, and
  `composer test` passes.

## Verify

- `php artisan test --filter="the admin table labels admin"` passes.
- `composer test` passes.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":1554,"specSha256":"76cd9e38e48556b5238267b218cea67b824511c09eb05613158fa14b82b6cd56","branch":"refs/heads/fix/admin-table-test-regex-hits-pcre-jit-limit","head":"399f04415665acf31514cabb86e1e20216d65137","baseRef":"refs/heads/main","baseCommit":"399f04415665acf31514cabb86e1e20216d65137","sourceTree":"98440370ab423afe7c881b7adf889948c6276e1a","landing":"local-merge","absentOptional":[]} -->
