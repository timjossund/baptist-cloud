import TrackPlayer from 'react-native-track-player';

import { PlaybackService } from './src/audio/service';

// The playback service must be registered before the app renders, so lock screen
// and notification controls work even when the app UI is not running.
TrackPlayer.registerPlaybackService(() => PlaybackService);

import 'expo-router/entry';
