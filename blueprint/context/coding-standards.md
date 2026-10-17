# Coding Standards

> Tuned by `/adopt` to the conventions this Laravel app already follows. Edit
> freely; these describe what the code does today, not an ideal.

## Stack

- PHP 8.2+, Laravel 12, MySQL
- Server-rendered Blade views; Livewire 4 for interactive pieces (search, post
  form, Markdown sandbox); Alpine.js for small client behavior
- Composer for PHP dependencies, npm for front-end tooling (Vite)
- Follow the Laravel Boost guidelines in `.agent/rules/` when they apply

## Project Structure

- Routes: `routes/web.php` (app), `routes/auth.php` (Breeze auth)
- Controllers: `app/Http/Controllers/[Resource]Controller.php`, resource-style
  method names (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`)
- Livewire components: `app/Livewire/` with views in `resources/views/livewire/`
- Models: `app/Models/`; jobs: `app/Jobs/`; policies: `app/Policies/`
- Page views: `resources/views/[page].blade.php`, grouped folders for `admin/`,
  `sermons/`, `profile/`, `auth/`
- Reusable Blade components: `resources/views/components/` (anonymous) and
  `app/View/Components/` (class-based)

## Naming

- Classes: PascalCase; methods and variables: camelCase
- Blade views and components: kebab-case file names
- Routes: named routes (`->name('create-post')`), kebab-case names
- Route model binding by `slug`, `id`, or `username` as the existing routes do

## Routing and Authorization

- Group routes by middleware: public, `auth` + `verified`, and `auth` +
  `verified` + `can:is-admin`
- Gates live in `AppServiceProvider` (`is-admin`, `viewHorizon`); `AdPolicy` exists
- Ownership checks for user-owned records happen in the controller
  (`$post->user_id != auth()->id() && ! auth()->user()->is_admin` then `abort(403)`)
- Rate limiters (`likeLimit`, `followLimit`) are defined in `AppServiceProvider`
  and applied with `throttle:` middleware

## Validation and Input

- Validate with `$request->validate([...])` in the controller; Form Requests are
  used for auth and profile updates
- User-supplied text is passed through `strip_tags()` before saving
- Markdown is rendered with `Str::markdown()` at display time
- Uploads are validated by mime type and size (`max:6048` for images)

## Data and Storage

- Eloquent models with explicit `$fillable`
- Schema changes go through new migrations in `database/migrations/`; never edit
  a migration that has already run in production
- `Model::automaticallyEagerLoadRelationships()` is on; still use `with()` and
  `withCount()` on list queries
- Files go to the S3-backed `postImages` and `sermons` disks; temporary uploads
  go to `local`
- Slow work (image resizing) runs in queued jobs under `app/Jobs/`, monitored by
  Horizon; a worker must be running locally (`composer dev` starts one)

## Styling

- Tailwind CSS utility classes in Blade, with `@tailwindcss/forms`
- `tailwind.config.js` and `postcss.config.js` are present alongside the
  `@tailwindcss/vite` plugin; keep using the existing setup
- Reuse the Breeze-style components (`primary-button`, `text-input`, `modal`,
  and so on) before writing new markup
- No inline styles

## Error Handling

- Use `abort(403)` / `abort(404)` for authorization and missing records
- Redirect back with `->with('success', '...')` flash messages after mutations
- Validation errors surface through `<x-input-error>`

## Formatting and Static Analysis

- Format PHP with Pint (`vendor/bin/pint`)
- Larastan runs at level 5 over `app/` (`vendor/bin/phpstan analyse`)

## Testing

The blueprint installs no test runner; testing is opt-in at the project level,
because the overlay can't know your stack. Adding unit testing is an explicit
setup task the AI can do through the normal workflow, either as a build-plan item
or with `/tests`. The setup should choose the stack-native runner, wire the
scripts or commands, add a small example test, and update the Commands section
of `AGENTS.md`.

When `AGENTS.md` declares a `Verify` command, treat it as the umbrella automated
gate. It combines only the checks this project actually has, in this order when
available: typecheck, tests, then build. The command does not enable an absent
test runner or replace focused evidence. It gives local work and optional CI one
exact command to run. `/ci` owns Verify and CI setup. `/tests` adds the real test
command to Verify when it already exists, but never creates CI only because
testing was configured.

**The opt-in switch is one signal: a `test` command in the Commands section of
`AGENTS.md`.** Declare one and **tests become a gate for logic-bearing steps**,
not an optional extra; leave it out and the loop verifies logic with the evidence
it already uses (run it, a screenshot, the build). Adding the runner is itself a
deliberate step, never a silent mid-step install. This is the single definition
of the switch; the skills and `ai-interaction.md` only point back here.

- **What to test (the scope rule):** logic where a wrong answer is possible -
  validation, authorization and ownership checks, slug builders, queued jobs, and
  controller actions that change data. In this Laravel app that usually means a
  Pest feature test hitting the route, with queues and disks faked.
- **What not to test:** visual layout and anything driving a real browser or
  external service (S3, Stripe, Turnstile). Verify those with a screenshot and
  the build, not brittle tests.
- **The gate (when a runner is configured):** a build step that adds in-scope logic
  must ship a passing test in the same reviewable diff. The project's test command
  must be green before the step is approved, before any checkpoint commit, and
  before `/complete` merges. UI and integration-only steps are exempt and ride on
  screenshot plus build evidence.
- **When it's named:** the `/feature` spec's Testing section predicts the coverage,
  `/implement` writes the test with the step, and if a step surfaces logic the spec
  didn't foresee, add a focused test then.
- An empty suite should fail, not pass, so "no tests ran" never looks like "passed".
- Test files live in `tests/Feature/` and `tests/Unit/`, following Laravel convention.
- Run them via the project's test command (see Commands in `AGENTS.md`), not a
  hardcoded tool name.

Stack binding: this project uses Pest with `pest-plugin-laravel`. Feature tests
live in `tests/Feature/`, unit tests in `tests/Unit/`. Use `Queue::fake()`,
`Storage::fake()`, and `Gate` overrides for queues, disks, and authorization, as
`tests/Feature/PostTest.php` does. Run tests with `php artisan test`.

## Browser Verification

For UI and integration behavior, prefer real browser evidence over reading the
code and assuming it works.

- Browser automation is separately opt-in through `/tests browser`. That setup
  reuses a compatible runner or prefers Playwright for supported projects, then
  documents the exact command as `Browser tests` in `AGENTS.md`.
- When `Browser tests` is declared, add focused coverage for stable behavioral
  done-whens when it is proportionate, and run the documented command during
  `/check`. Do not assume it proves visual fidelity, real authenticated-profile
  behavior, browser chrome, or another claim the test does not observe.
- If no Browser tests command is declared, do not add a runner silently in the
  middle of an unrelated feature. Use the available dev server, browser
  screenshots, build output, API output, or manual evidence instead.
- Browser tests are not part of the default Verify command or CI unless the user
  separately chooses that slower gate.
- Browser evidence is especially important for flows that click, type, submit,
  navigate, download files, render complex layouts, or depend on client-side
  state.

## Code Quality

- No commented-out code unless specified
- No unused imports or variables
- Keep functions under 50 lines when possible

## Comments

Write code that explains itself; comment only what the code cannot say.
Over-commenting is a common AI tell, so resist it.

- Comment the **why**, not the **what**. Delete any comment that restates the code.
- No banner/header blocks, section dividers, or step-by-step narration of obvious
  code. A file does not need a comment announcing each region.
- A comment earns its place only when it captures something the code can't: a
  non-obvious decision, a gotcha or workaround, why a value is what it is, or a
  link to a spec or issue.
- Prefer self-documenting names and small functions over explanatory comments.
- Keep doc comments minimal: a one-line purpose on an exported type or function is
  plenty; don't write JSDoc that just repeats the signature.
- When in doubt, leave the comment out.

## Writing

- No em dashes (U+2014) in generated content: docs, comments, commit messages,
  READMEs, specs. They read as AI-generated.
- Use a hyphen for `term - description` separators; rephrase prose with commas,
  parentheses, or a colon. Avoid en dashes and the ellipsis character too.
