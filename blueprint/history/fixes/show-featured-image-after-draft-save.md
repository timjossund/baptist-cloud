# Fix: Show featured image after draft save

**Type:** Fix
**Status:** verified
**Branch:** fix/show-featured-image-after-draft-save

## The problem

Saving a new draft redirects straight to `/post/{slug}/edit`. The featured
image is still in the queue there (`ProcessPostImage`), so
`posts.image` is null. The `Post::image()` accessor then falls back to
`/default-avatar.png`, which doesn't exist in `public/`. The edit page shows a
broken-image icon and asks the author to refresh.

The image only appears after a manual refresh, so the upload looks broken.

## The fix

Make the edit page's "Current Featured Image" area update itself while the
image is processing:

- Add a small Livewire component, `App\Livewire\PostImagePreview`, with view
  `livewire/post-image-preview.blade.php`. It takes the `Post` as a
  `#[Locked]` property and refreshes it on each render.
  - While `image` is null, show a grey "Processing image…" placeholder and the
    still-processing message, and poll with `wire:poll.2s`.
  - Once `image` is set, render the `<img>` and stop polling, because
    `wire:poll` is left out.
- In `edit-post.blade.php`, replace the plain `<img>` and its
  `@unless` message with `<livewire:post-image-preview :post="$post" />`.

It reuses Livewire 4, which is already installed. There are no new routes,
endpoints, or JavaScript.

It must not break anything:

- `ProcessPostImage`, the controllers, and the queue flow stay unchanged.
- Posts that already have an image render the `<img>` with no polling.
- The edit form's fields and submit behavior don't change. The component only
  renders a `<div>` inside the form.

Out of scope: the missing `/default-avatar.png` fallback used elsewhere, and
the profile page briefly showing an old image after an image update.

## Build steps

1. Add `PostImagePreview` and its view, wire it into `edit-post.blade.php`,
   and add a Livewire test in `tests/Feature/PostTest.php`. This step is
   already drafted in the working tree, so `/implement` reviews and verifies
   it. **Done when:**
   - With no image, the component shows "still processing" and renders
     `wire:poll`.
   - After `image` is set and the component refreshes, it renders that image
     URL and no longer renders `wire:poll`.
   - The existing "new post edit page explains the image is still processing"
     test still passes.
   - `composer test` passes.

## Verify

- `composer test`, `vendor/bin/pint --test`, and `vendor/bin/phpstan analyse`
  are clean.
- Manual: with `composer dev` running (queue worker included), create a draft
  with an image. The edit page shows the "Processing image…" placeholder, then
  the real image a few seconds later without a refresh.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":2710,"specSha256":"6fc6ad127ef26492d14d2451cbdc620c96abb50f2efded9d804d0b72df0a2c9a","branch":"refs/heads/fix/show-featured-image-after-draft-save","head":"0f15eb69a44a16f7fc98bbf05bb14d741e62c4f9","baseRef":"refs/heads/main","baseCommit":"0f15eb69a44a16f7fc98bbf05bb14d741e62c4f9","sourceTree":"da667e3c609f0a37583cf679680e5dba48f3daea","landing":"local-merge","absentOptional":[]} -->

## Try it now

Run or do:

1. Start the app with its queue worker: `composer dev`.
2. Open http://baptist-cloud.test/post/create-post, add a title, category, body, and a JPG featured image, then save the draft.

Expected:

The edit page first shows a grey "Processing image…" box with a still-processing note. Within a few seconds, the real featured image replaces it without a refresh.

This proves:

The edit page picks up the queued image on its own instead of showing a broken image until you refresh.
