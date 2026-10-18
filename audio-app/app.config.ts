import type { ConfigContext, ExpoConfig } from 'expo/config';

// APP_VARIANT picks the build: `development` installs beside a store build and may
// reach the local site over plain http; anything else is the production app.
const isDev = process.env.APP_VARIANT === 'development';

export default ({ config }: ConfigContext): ExpoConfig => ({
  ...config,
  name: isDev ? 'Baptist Cloud Dev' : 'Baptist Cloud',
  slug: 'baptist-cloud-audio',
  version: '1.0.0',
  orientation: 'portrait',
  icon: './assets/images/icon.png',
  scheme: isDev ? 'baptistcloud-dev' : 'baptistcloud',
  userInterfaceStyle: 'automatic',
  ios: {
    bundleIdentifier: isDev ? 'cloud.baptist.audio.dev' : 'cloud.baptist.audio',
    icon: './assets/expo.icon',
    infoPlist: {
      UIBackgroundModes: ['audio'],
      ...(isDev && {
        NSAppTransportSecurity: {
          NSAllowsLocalNetworking: true,
          NSExceptionDomains: {
            'baptist-cloud.test': { NSExceptionAllowsInsecureHTTPLoads: true },
          },
        },
      }),
    },
  },
  android: {
    package: isDev ? 'cloud.baptist.audio.dev' : 'cloud.baptist.audio',
    allowBackup: false,
    adaptiveIcon: {
      backgroundColor: '#E6F4FE',
      foregroundImage: './assets/images/android-icon-foreground.png',
      backgroundImage: './assets/images/android-icon-background.png',
      monochromeImage: './assets/images/android-icon-monochrome.png',
    },
    permissions: [
      'android.permission.FOREGROUND_SERVICE',
      'android.permission.FOREGROUND_SERVICE_MEDIA_PLAYBACK',
      'android.permission.POST_NOTIFICATIONS',
    ],
    predictiveBackGestureEnabled: false,
  },
  plugins: [
    'expo-router',
    'expo-secure-store',
    ['expo-build-properties', { android: { usesCleartextTraffic: isDev } }],
    [
      'expo-splash-screen',
      {
        backgroundColor: '#208AEF',
        image: './assets/images/splash-icon.png',
        imageWidth: 76,
      },
    ],
  ],
  experiments: {
    typedRoutes: true,
    reactCompiler: true,
  },
});
