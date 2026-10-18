# 0001. Audio app stack and architecture: rationale

## Context

Baptist Cloud is adding a listen only phone app for iOS and Android. Paying members sign in with their website account, browse sermons, and play them. React Native is already chosen. Everything else about the app's foundation is open: how the native projects are set up and upgraded, how screens are organised, how audio keeps playing when the phone is locked, where the sign in token is kept, and how builds reach testers and the stores.

The forces are a small team that mainly works in Laravel, a web API that already exists (`POST /api/v1/auth/token`, `GET /api/v1/me`, JSON errors with `{ message, code }`, any `401` means sign in again), and the core promise of the product: a sermon must keep playing with lock screen controls on both platforms. That promise needs native background audio work on each platform, which is the riskiest part of the stack. The token is a long lived credential and must sit in the platform's secure storage, not in plain app storage.

Without a decision, slice 1 would start on an unknown native build and could stall on Xcode or Gradle problems mid feature. Later deferred features (CarPlay and Android Auto, offline downloads, push) also depend on the audio and project setup chosen now.

## Options considered

### Option 1: Expo with continuous native generation

Expo SDK 58 with `npx expo prebuild`. Native folders are generated from `app.config.ts` and config plugins and are not committed. Development builds (`expo-dev-client`) load native libraries such as react-native-track-player. Expo Router for navigation, expo-secure-store for the token, EAS Build and EAS Update for shipping.

**Pros**:
- Upgrades are mostly version bumps; Expo regenerates the native projects (basis: Expo continuous native generation).
- First party modules for secure storage, updates, and routing, maintained in step with the SDK.
- EAS handles iOS certificates and Android keystores, the part of mobile release a web team usually gets wrong.

**Cons**:
- Any native change must be expressed as a config plugin; quick hand edits in `ios/` are lost.
- Ties store builds to an Expo account and its plan limits.

### Option 2: Bare React Native CLI

The plain React Native 0.88 template with `ios/` and `android/` committed, React Navigation, react-native-keychain, and fastlane for builds.

**Pros**:
- Full control of every native file; no external build service.
- No dependence on Expo's release schedule.

**Cons**:
- Every React Native upgrade means merging native project diffs by hand, which is the most common reason small teams fall behind on versions.
- Signing, certificates, and store upload are all on the team.

### Option 3: Expo with native folders committed

Expo modules and tooling, but `ios/` and `android/` are generated once and then edited and committed.

**Pros**:
- Expo's libraries and EAS, plus freedom to edit native code directly.

**Cons**:
- Loses the clean upgrade path that is the main reason to use Expo; native folders drift from config.

**Library choices inside Option 1** (picked during the design conversation):
- Audio: react-native-track-player over expo-audio. Track player is purpose built for long form audio (queue, remote commands, playback speed, car audio later). Runner up expo-audio is simpler and first party but thinner on remote commands and car audio; it is the fallback if track player breaks on an SDK upgrade.
- Token storage: expo-secure-store over react-native-keychain. One token needs simple key value storage; keychain's biometric options are not needed yet.
- Navigation: Expo Router over React Navigation directly. Same engine underneath, with typed routes and deep links built in.
- Package manager: npm over pnpm or bun. Already installed and used by the Laravel side.

## Rationale

The riskiest promise is background audio with lock screen controls on two platforms, and the team's strength is the web. Option 1 keeps native upkeep closest to zero while still allowing a native audio library, which Expo Go alone would not. Generated native folders mean upgrades stay cheap over the app's life, which matters more than the occasional need to write a config plugin.

Option 2 gives control the team does not need yet and charges for it on every upgrade and release. Option 3 takes Expo's tools but gives up the upgrade path that justifies them.

The scaffold installs track player and secure store now because a known track player build failure on a newer Expo SDK shows the risk is real. Proving both compile on SDK 58 before slice 1 turns a mid slice surprise into a foundation task. EAS Update is chosen but not wired, so the scaffold stays lean until there is a store build to update.

## References

**Project sources**:
- `docs/scope/audio-app/scope.md`, feature 1 and the build approach (Tracer Bullet)
- `docs/specs/web/0002-mobile-api-token-sign-in/index.md`, the token sign in and error contract the app consumes
- `AGENTS.md`, proportional engineering (smallest reversible design, reuse installed tools)

**Practices & standards**:
- Store long lived credentials in platform secure storage (Keychain, Keystore), never plain app storage
- Generated native projects from config (Expo continuous native generation)

**Links** (web verified during the design conversation):
- Expo SecureStore: https://docs.expo.dev/versions/latest/sdk/securestore/
- EAS local builds: https://docs.expo.dev/build-reference/local-builds/
- Track player with Expo guide: https://rntp.dev/docs/next/guides/with-expo
- Android emulator networking: https://developer.android.com/studio/run/emulator-networking-address
