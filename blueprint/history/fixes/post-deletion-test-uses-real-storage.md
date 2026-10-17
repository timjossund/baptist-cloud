# Fix: Post deletion test uses real storage

**Type:** Fix
**Status:** verified
**Branch:** fix/post-deletion-test-uses-real-storage

## The problem

The test suite can reach the real IONOS S3 buckets. In
`tests/Feature/PostAccessTest.php`, the test "only the author or an admin can
delete a post" calls `PostController@destroy`. That method runs
`Storage::disk('postImages')->delete(...)`, and the test never fakes the
`postImages` disk. So:

- In the main checkout, the test sends a real delete request to the
  production bucket, using whatever IONOS credentials are in `.env`.
- In a checkout without those credentials, such as a fresh worktree or CI, it
  fails with "Missing required client configuration options: region".

The root cause is that faking the S3 disks is left to each test. Only
`PostTest` fakes `postImages`, and no test fakes `sermons`. Any future test
that reaches a sermon or post-image code path would hit real storage too.

## The fix

Fake both S3-backed disks for every feature test in one place: a `setUp()` in
`tests/TestCase.php` that calls `Storage::fake('postImages')` and
`Storage::fake('sermons')` after `parent::setUp()`. Pest's
`pest()->extend(TestCase::class)` already applies `TestCase` to `tests/Feature`.

It must not break anything:

- Tests that already call `Storage::fake('postImages')` keep working. Calling
  the fake again just gives a fresh fake disk.
- The `local` and `public` disks are not faked globally. `PostTest` already
  fakes `local` where it needs to, and both are local filesystems.
- No application code changes.

## Build steps

1. Add the global S3 disk fakes in `tests/TestCase.php`. **Done when:**
   - `composer test` passes, 87 tests, in the main checkout.
   - The same suite passes with the IONOS settings blanked, for example
     `IONOS_DEFAULT_REGION= IONOS_ACCESS_KEY_ID= composer test`. That proves
     no test needs real storage credentials.

## Verify

- `composer test` passes as-is.
- With the IONOS env values blanked, `composer test` still passes. The delete
  test passes in that run, where it failed before.
- `vendor/bin/pint --test` is clean.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":2130,"specSha256":"5abf8bf6d6d9830c528c81068943d69def6d659d154d8418d94d18ab15e7e75b","branch":"refs/heads/fix/post-deletion-test-uses-real-storage","head":"76d729281b9e08b4faeb7bd066999605d4f66dd9","baseRef":"refs/heads/main","baseCommit":"76d729281b9e08b4faeb7bd066999605d4f66dd9","sourceTree":"9ff16d5ae15bf3e5377ce0d450ba8f7c7d7bb5ed","landing":"local-merge","absentOptional":[]} -->

## Try it now

Run or do:

```bash
IONOS_DEFAULT_REGION= IONOS_ACCESS_KEY_ID= IONOS_SECRET_ACCESS_KEY= composer test
```

Expected:

`Tests: 87 passed`, including "only the author or an admin can delete a post".

This proves:

No test needs, or reaches, the real IONOS storage buckets.
