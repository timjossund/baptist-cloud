# Scope: Baptist Cloud

Baptist Cloud is a publishing and community platform for Baptist pastors and writers. This repo now holds two apps: the Laravel website (repo root) and a new React Native listening app (`audio-app/`) that lets paying members play sermons on their phone.

_These are recommendations to keep your build orderly, not requirements. Skip anything that does not fit._

## Workspaces

| Workspace | Scope | What it is | Rollup |
|---|---|---|---|
| web | [web/scope.md](web/scope.md) | The Laravel website at the repo root, plus the new mobile API and Member plan | 18 existing, 2 of 5 planned done |
| audio-app | [audio-app/scope.md](audio-app/scope.md) | The React Native listening app for iOS and Android (greenfield) | 0 of 9 planned done |

## Suggested order across both apps

The first slice runs end to end, from the website's billing through the API to the phone, so the work alternates between the two scopes. App foundations (audio-app 1 to 4) have no web dependency, so you can do them while the web pieces are in progress.

1. web 19 · Member plan (done)
2. web 20 · Mobile API and token sign in (done)
3. audio-app 1 to 4 · App foundations
4. web 21 + audio-app 5 · Slice 1: sign in and play a sermon
5. audio-app 6 · Slice 2: full player
6. web 22 + audio-app 7 · Slice 3: search and series
7. web 23 + audio-app 8 · Slice 4: follow authors
8. audio-app 9 · Store release

## Legend

Each workspace scope carries its own legend. A feature that spans both apps is split into a web half (the API) and an app half (the screens), each pointing at the other.
