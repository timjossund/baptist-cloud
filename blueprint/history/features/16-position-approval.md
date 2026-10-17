# Feature: Position approval

**From build-plan:** feature 16
**Build attempt:** 1
**Branch:** feature/position-approval
**Status:** verified

## Goal

Stop unreviewed position listings from going live. Any verified user can still
submit a position. New listings stay hidden from everyone except the poster
and admins until an admin approves them. Posters can edit their own listings,
and admins approve or reject pending ones from a review queue.

## In scope

- Record who posted each listing and whether it has been approved.
- Mark every existing listing as approved, so nothing currently live disappears.
- New submissions start as pending. The poster sees a "submitted for review"
  message instead of "created".
- `/positions` shows approved listings to everyone. Signed-in posters also see
  their own pending listings there, marked "Pending review".
- `/position/{id}` returns 404 for a pending listing unless the viewer is its
  poster or an admin. Those two see a "Pending review" label.
- Posters can edit their own listings, and admins can edit any listing. Anyone
  else gets 403.
- When a poster edits an already-approved listing, it goes back to pending.
  When an admin edits a listing, its status stays the same.
- Admin review queue at `/admin/pending-positions`. It lists pending listings
  oldest first, with View, Approve, and Reject actions. Reject deletes the
  listing after a browser confirm dialog.
- A "Pending Positions" button on the admin dashboard that shows the pending
  count.

## Out of scope

- Email or other notifications to admins or posters
- Posters deleting their own listings, and listing expiry
- Changes to the listing form fields, plus the unused `published_at` field and
  its validation
- Extra rate limiting on submissions. Routes already require a signed-in user
  with a verified email.
- Any change to subscriptions (feature 17)

## Build loop

`workflow.stepReview` is `feature`, so build all steps, then present one review
packet. `workflow.checkpointCommits` is `disabled`, so there are no step commits.
`/complete` creates the single feature commit. `composer test` must pass at the
end of each logic step.

## Build steps

- [x] **1. Ownership and approval columns.** Add a new migration that adds
  `listings.user_id` and `listings.approved_at`, and backfills existing rows
  with `approved_at = created_at` (or `now()` when `created_at` is null). On
  `Listing`, add a `user()` belongsTo relation, cast `approved_at` to datetime,
  add an `approved()` query scope (`whereNotNull('approved_at')`), and add an
  `isApproved()` helper. Keep `user_id` and `approved_at` out of `$fillable`.
  Add a `ListingFactory` with an approved default and a `pending()` state.
  **Done when:** `php artisan migrate` succeeds locally, the existing local
  listings still appear on `/positions`, and `composer test` passes.

- [x] **2. Pending submissions and visibility.** `store` sets
  `user_id = auth()->id()`, leaves `approved_at` null, and redirects to
  `/positions` with "Position submitted. It will appear once an admin approves
  it." `showPositions` returns approved listings plus the viewer's own pending
  ones (`approved()->orWhere('user_id', auth()->id())`, newest first, cursor
  pagination unchanged). Each pending card shows a "Pending review" badge.
  `showPosition` aborts 404 for a pending listing unless the viewer is its
  poster or an admin, and shows a "Pending review" label when it renders a
  pending one. **Done when:** tests cover pending creation with the owner set,
  approved-only listings for a different user, own pending listings shown to
  the poster, and 404 versus 200 on the single page for a stranger, the poster,
  and an admin.

- [x] **3. Owner editing.** `edit` and `update` allow the poster
  (`$listing->user_id === auth()->id()`) or an admin. Everyone else gets
  `abort(403)`, using the project's existing controller ownership-check style.
  When the poster saves an approved listing, `update` sets `approved_at` to
  null and flashes "Changes saved. Your listing will reappear once an admin
  approves it." Admin saves keep `approved_at` unchanged. On `/positions`, the
  edit icon shows for admins and for the poster's own listings. **Done when:**
  tests cover a poster editing their own listing, a poster's edit returning an
  approved listing to pending, an admin edit keeping it approved, and another
  user getting 403 on both edit and update. The existing admin and non-admin
  edit tests still pass.

- [x] **4. Admin review queue.** Add these routes to the existing `auth`,
  `verified`, `can:is-admin` group:
  - `GET /admin/pending-positions` (`admin.pending-positions`)
  - `POST /admin/positions/{listing}/approve` (`approve-listing`), which sets
    `approved_at = now()` and redirects back with "Position approved"
  - `DELETE /admin/positions/{listing}` (`reject-listing`), which deletes the
    listing and redirects back with "Position rejected"

  Add a `resources/views/admin/pending-positions.blade.php` view modeled on
  `reported-posts.blade.php`. It lists position, church, city and state,
  submitted date, and the poster's name (or "Unknown" when the poster has
  none), with paginated links and an empty state reading "No positions
  awaiting review." The Reject form uses `onsubmit="return confirm(...)"`. Add
  a "Pending Positions (n)" button on `admin/admin.blade.php` next to Reported
  Posts. **Done when:** tests cover an admin approving a listing (it then
  appears to other users), an admin rejecting one (it is deleted), and a
  non-admin getting 403 on all three routes. The queue page renders pending
  listings and the empty state.

## Files / areas

- `database/migrations/<new>_add_owner_and_approval_to_listings_table.php` (new)
- `database/factories/ListingFactory.php` (new)
- `app/Models/Listing.php`
- `app/Http/Controllers/ListingController.php`, which covers submission,
  visibility, and owner editing
- `app/Http/Controllers/AdminController.php`, which covers the queue, approve,
  and reject actions
- `routes/web.php`, admin group
- `resources/views/positions.blade.php`, `single-position.blade.php`
- `resources/views/admin/admin.blade.php`, `admin/pending-positions.blade.php` (new)
- `tests/Feature/ListingTest.php`

## Data / contracts

- `listings.user_id`: `foreignId`, nullable, constrained to `users`,
  `nullOnDelete()`. It is nullable because existing rows have no known poster.
  Deleting a user keeps their listings, with no owner.
- `listings.approved_at`: `timestamp`, nullable. Null means pending, and
  not-null means live. The migration's `down()` drops the foreign key and both
  columns.
- The status is derived from `approved_at`, with no separate status column.
  Rejection deletes the row, so no rejected state is stored.
- The trusted actor always comes from `auth()`. `user_id` and `approved_at`
  are never read from request input.
- Pending listings are hidden with 404, not 403, so a stranger can't confirm
  that a pending listing exists.
- Existing escaping stays: text goes through `strip_tags` on save, and
  `Markdown::render` runs at display time.

## Testing

Pest feature tests in `tests/Feature/ListingTest.php`, run with `composer test`.
The scenarios are listed in each step's Done when. The existing four listing
tests must keep passing. Update the "created" test to assert the new pending
state and owner. Larastan (`vendor/bin/phpstan analyse`) and Pint run before
the review packet. Visual layout of the badges and queue page is checked in the
browser at http://baptist-cloud.test, not with automated tests.

## Notes for the AI

- Follow the existing controller ownership style in coding standards. Don't
  add a Policy for this.
- `edit` and `update` take `$id` today. Keep that signature or switch to route
  model binding, but leave the URLs unchanged.
- The `orWhere` in `showPositions` must be grouped so that it can't widen any
  other constraint added later.
- Guests can't reach any listing route today, because they all sit in the
  `auth` + `verified` group. Keep it that way.

## Open questions

- **Should a poster's edit to an already-approved listing send it back for
  review?** This spec says yes, because otherwise a poster could get approved
  and then change the listing to anything. If you'd rather keep edits live,
  drop that rule from step 3.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":8354,"specSha256":"db0dc144a9ee0780b688bb6540ef716b3de3c18b4ab9d2045adf0e3efb02fb2d","branch":"refs/heads/feature/position-approval","head":"ae72cbd03c458e0d949001337f273aa2aa3dd69f","baseRef":"refs/heads/main","baseCommit":"b6496a38a140e2f166e81ae2b25c89dd7126304f","sourceTree":"0e4800438c173d2effe97716325626fa95ffba42","landing":"local-merge","absentOptional":[]} -->

## Independent review

**Status:** passed
**Target commit:** ae72cbd03c458e0d949001337f273aa2aa3dd69f
**Base commit:** b6496a38a140e2f166e81ae2b25c89dd7126304f
**Base ref:** main
**Spec hash:** db0dc144a9ee0780b688bb6540ef716b3de3c18b4ab9d2045adf0e3efb02fb2d
**Prepared by:** claude
**Builder model:** claude-opus-5-5
**Requested reviewer:** claude
**Requested model:** claude-opus-5-5
**Requested execution:** automatic
**Requested at:** 2026-10-17T19:31:15Z
**Workflow:** regular
**Check required:** no
**Reviewer adapter:** claude
**Reviewer model:** claude-opus-5-5
**Reviewer context:** fresh subagent
**Actual execution:** automatic
**Reviewed at:** 2026-10-17T19:32:17Z
**Scope:** current
**Lenses:** quality, security, performance, tests
**Verdict:** passed
**Check result:** not-required

### Commands

- `composer test`: pass (78 tests, 225 assertions)
- `vendor/bin/phpstan analyse --no-progress`: pass (no errors)
- `vendor/bin/pint --test`: pass

### Evidence

- Freshness verified: HEAD equals target; `git merge-base main HEAD` equals base; spec SHA-256 matches; spec is tracked (no snapshot); only `blueprint/context/review.md` differs from target.
- Reviewed all 12 changed paths in the range: `AdminController`, `ListingController`, `Listing` model, `ListingFactory`, the new migration, admin/positions/single-position/pending-positions views, `routes/web.php`, `tests/Feature/ListingTest.php`, and the spec.
- Security: `user_id` and `approved_at` stay out of `$fillable` and come only from `auth()`/admin actions (covered by the request-injection test); `showPositions` groups the `orWhere` in a closure; pending listings return 404 to non-owner non-admins; edit/update load the record before the ownership check and follow the controller `abort(403)` style in coding standards; new admin routes sit in the `auth`+`verified`+`can:is-admin` group with CSRF and method spoofing; Blade output is escaped.
- No other listing surfaces exist (grep of `app/`, `resources/`, `routes/`), so no pending-listing leak path outside the reviewed controllers.
- Migration runs under the in-memory SQLite test suite; backfill uses portable `COALESCE(created_at, CURRENT_TIMESTAMP)`.
- Performance: queue and positions are paginated; queue eager-loads `user`; dashboard adds one count query.

### Findings

- None

### Remaining risk

- Visual layout of badges and queue page not checked in a browser (Check not required; spec assigns this to manual browser review).
- Migration `down()` and the backfill were not exercised against the local MySQL/Herd database; only the SQLite test migration ran.
- Minor race: if a poster edits a pending listing between an admin viewing it and clicking Approve, the admin approves content they did not see. Low impact; not reported as a finding.

## Try it now

Run or do:

1. Sign in as a regular user at http://baptist-cloud.test.
2. Submit a position at `/create-listing`.
3. Sign in as an admin and open `/admin/pending-positions`.

Expected:

- After submitting, you land on `/positions` with "Position submitted. It will appear once an admin approves it." Your new listing shows a yellow "Pending review" badge.
- The admin queue lists that position with your name. After you click Approve, it shows on `/positions` without the badge.

This proves:

New positions stay hidden until an admin approves them, and approving one makes it live.
