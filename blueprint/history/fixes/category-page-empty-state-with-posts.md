# Fix: Category page empty state with posts

**Type:** Fix
**Status:** verified
**Branch:** fix/category-page-empty-state-with-posts

## The problem

`/category/{title}` (`PostController::category`) lists every published post in
that category, whoever wrote it. It renders the shared `home-page.blade.php`,
which shows the "Hmmm... nothing here. Let's find something to read!" empty
state for signed-in users when:

```blade
auth()->user()->following()->count() === 0 || $posts->count() === 0
```

The `following()->count() === 0` half only makes sense on the home feed
(`index`), which filters posts to followed authors. On a category page, an
author who follows nobody sees the empty-state message and Search button above
the posts, including their own new article. The query is fine; only the message
is wrong.

## The fix

Show the empty state only when there are no posts to list:

- In `home-page.blade.php`, change the condition to `$posts->isEmpty()`.
- The home feed still shows the message to users who follow nobody, because
  `index()` filters with `whereIn('user_id', [])` and gets no posts.

It must not break anything:

- No controller or query changes.
- Guests still don't see the empty state, because it stays inside `@auth`.
- Headings, post items, ads, and pagination don't change.

Out of scope: the category query doesn't eager-load `user`/`category` the way
`index()` does. That's a performance issue, not this bug.

## Build steps

1. Update the empty-state condition and add feature tests in
   `tests/Feature/PostTest.php`. **Done when:**
   - A signed-in user who follows nobody opens a category with a published
     post: the page shows the post title and not "nothing here".
   - The same user opens a category with no published posts: the page shows
     "nothing here".
   - A signed-in user who follows nobody opens `/`: the page shows "nothing
     here".
   - `composer test` passes.

## Verify

- `composer test`, `vendor/bin/pint --test`, and `vendor/bin/phpstan analyse`
  are clean.
- Manual: signed in as an author who follows nobody, publish a post in a
  category and open that category's tab. The post appears with no "nothing
  here" message above it.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":2196,"specSha256":"bdda94518cd26f0c06cf652d23ea1120414987114f50cc4adae36db0d854a82b","branch":"refs/heads/fix/category-page-empty-state-with-posts","head":"cfb7d882d35bda4dee967e12db13f2979d315ba7","baseRef":"refs/heads/main","baseCommit":"cfb7d882d35bda4dee967e12db13f2979d315ba7","sourceTree":"c865e8bf121231a0c9af33015b337705aebcbc2a","landing":"local-merge","absentOptional":[]} -->

## Try it now

Run or do:

1. Sign in as an author who follows nobody.
2. Open the tab for a category that has one of your published posts, for example http://baptist-cloud.test/category/Practice.

Expected:

Your post appears, and "Hmmm... nothing here" does not appear above it. A category with no published posts still shows the message.

This proves:

The empty-state message depends on whether the page has posts, not on who you follow.
