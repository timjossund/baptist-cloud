# Scope: Baptist Cloud audio app

A React Native app for iOS and Android where listeners sign in with their Baptist Cloud account and, with an active listener (or author) plan, browse and play sermons on their phone. Listen only: authors keep uploading on the website, and people subscribe on the website, not in the app.

**Build approach:** Tracer Bullet (prove one real path end to end, sign in on the phone and play one sermon through the real API, then thicken it one strand at a time).
**Workflow:** Beta (after `/develop`: `/check verify`, then `/test`). The project default level of rigor. `/architect` is the recommended first stop for a feature with a real decision, but skippable when you already know the build. Any feature can carry its own tag (e.g. `· GA`) to do more or less.

_These are recommendations to keep your build orderly, not requirements. Skip anything that does not fit: if you already know how to build a feature, use `/develop` and skip `/architect`. You decide when a feature is `done`._

## At a glance

| # | Feature | Phase | Status |
|---|---------|-------|--------|
| 1 | Stack & architecture | Foundation | planned |
| 2 | Coding standards & tooling | Foundation | planned |
| 3 | API contract & local data | Foundation | planned |
| 4 | Design system & UI foundation | Foundation | planned |
| 5 | Sign in and play a sermon | Slice 1 | planned |
| 6 | Full player | Slice 2 | planned |
| 7 | Search and series | Slice 3 | planned |
| 8 | Follow authors | Slice 4 | planned |
| 9 | Store release | Release | planned |

## Foundations

### 1. Stack & architecture · needs a decision
React Native is set; decide the rest of the app's stack (project setup, navigation, background audio playback, secure token storage, build and update path) and scaffold a runnable app in `audio-app/`.
**Done when:** the stack is recorded in a spec, and the empty app boots on an iOS simulator and an Android emulator.
- [ ] Decide the stack (spec): `/architect audio app stack & architecture`

### 2. Coding standards & tooling
Capture the app's conventions in `audio-app/AGENTS.md`, then install lint, format, type checks, and tests from the real scaffold.
**Done when:** `audio-app/AGENTS.md` reflects the real stack, and lint, format, and type checks run clean.
- [ ] Capture conventions + tooling choices: `/audit audio-app`

### 3. API contract & local data · needs a decision
The shapes the app reads from the web API (listener, sermon, series, author) and what the phone keeps locally: the sign in token, recently played, and resume positions.
**Done when:** the app's data types match the web API in features web 20 and 21, and the spec says what is stored on the phone, what is cached, and what is cleared on sign out.
- [ ] Design it (spec): `/architect audio app API contract & local data`

### 4. Design system & UI foundation · needs a decision
A visual language for the app that feels like Baptist Cloud, base components (list rows, sermon card, buttons, player controls), and accessibility from day one: screen reader labels, larger text sizes, and light and dark mode.
**Done when:** `design.md` covers type, color, spacing, and components, and base components work with VoiceOver and TalkBack.
- [ ] Design it (spec): `/architect audio app design system`

## Slice 1: Sign in and play a sermon

### 5. Sign in and play a sermon · needs a decision
The core loop and walking skeleton (pairs with web 19, 20, 21): sign in with a Baptist Cloud account, see the newest sermons, open one, and play it with background audio and lock screen controls. Without listening access, the app says a plan is needed, following store rules for apps where people subscribe elsewhere.
**Done when:** a subscriber signs in, plays a sermon, locks the phone, and it keeps playing with lock screen controls on both platforms; a non subscriber sees the plan needed message; sign out clears the token.
- [ ] Design it (spec): `/architect sign in and play a sermon`

## Slice 2: Full player

### 6. Full player · needs a decision
Thicken playback: a mini player that stays visible across screens, a full player with seek, skip back and forward, playback speed, and resume where you left off.
**Done when:** playback continues while browsing, speed and skip work, and reopening a sermon resumes at the last position.
- [ ] Design it (spec): `/architect full player`

## Slice 3: Search and series

### 7. Search and series
The app half of slice 3 (pairs with web 22): a search screen for sermons and authors, series pages listing their sermons in order.
**Done when:** a listener finds a sermon by title or author, opens a series, and plays from it; empty and error states render.
- [ ] Build it: `/develop search and series`

## Slice 4: Follow authors

### 8. Follow authors
The app half of slice 4 (pairs with web 23): author pages, follow and unfollow, and a Following tab with sermons from followed authors.
**Done when:** following an author in the app adds their sermons to the Following tab and shows on the website too.
- [ ] Build it: `/develop follow authors`

## Release

### 9. Store release · needs a decision · GA
Everything to ship to the App Store and Google Play: icons and splash, store listings, privacy labels, signed builds, beta testing, and review notes explaining that subscriptions are bought on the website.
**Done when:** both apps pass store review and are live, with a repeatable way to ship the next build.
- [ ] Design it (spec): `/architect store release`

## Deferred
Out of scope for the current build pass, kept so the plan stays honest.
- **Offline downloads**: save sermons to listen without a connection · needs a decision
- **New sermon notifications**: push alerts when a followed author posts · needs a decision
- **Car audio**: CarPlay and Android Auto support · needs a decision
- **Reading articles in the app**: once the listener plan covers reading · needs a decision
- **Author uploads**: upload sermon audio from the phone · needs a decision
- **Product analytics**: measure listening and retention · needs a decision

## Legend

**The decision box.** Every feature carries exactly one, the sub task whose label ends with `(spec)`. Skills locate it by that `(spec)` suffix, never by an exact label. Every other box is an execution box and `/architect` never ticks one.

**Feature lifecycle**: the scope updates as a feature moves; each row is what it shows and who sets it:

| State | Set by | The feature shows |
|---|---|---|
| `planned` · needs a decision | `/scope` | one box: `Design it (spec): /architect <feature>` |
| `in-progress` (designed) | **`/architect` at spec capture** | `Design it` ticked; spec linked; `Build it: /develop <feature>` + **2 to 5 milestones**; the tier's closing boxes (`Verify it` Alpha+, `Test it` Beta+, `Review it` + `Document it` GA) |
| `in-progress` (building) | `/develop` | milestone sub boxes tick one by one; code pointer filled |
| `in-progress` (verified) | `/check verify` | `Build it` + milestones ticked; `Verify it` ticked |
| `done` | **you, when you decide it is**; `/sync` reconciles | boxes you ran ticked, skipped ones marked skipped |

- **Next step** = the first unticked box (always a command or a tracked milestone).
- **needs a decision** = run `/architect` first; otherwise straight to `/develop` (or `/audit` for standards & tooling). The tag drops once the spec is captured.
- **Atomic build tasks live in the spec's `## Build plan`, not here**: the scope carries only the milestone rollup.
- **Status** `planned` → `in-progress` → `done`, plus `existing` (shipped before this workflow) and `dropped` (de-scoped, kept for history).
- **Workflow tier tag** beside a heading (e.g. `· GA`) sets that one feature's rigor above the project default; no tag inherits the default.
- **Workflow** (header line) is the project default, what runs after `/develop`: **Prototype** = nothing; **Alpha** = `/check verify`; **Beta** = `/check verify` then `/test`; **GA** = adds a fresh model `/check review` then `/document`.
- **Pointer line** (`spec <n> · code in <path>`): the spec link added by `/architect`, the code path by `/develop`.
