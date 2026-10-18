import { Stack, useTheme } from 'expo-router';
import { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import TrackPlayer from 'react-native-track-player';

type PlayerState = 'starting' | 'ready' | 'failed';

export default function HomeScreen() {
  const { colors } = useTheme();
  const [player, setPlayer] = useState<PlayerState>('starting');

  // Proves the native audio module loads. Feature 5 replaces this screen.
  useEffect(() => {
    TrackPlayer.setupPlayer()
      .then(() => setPlayer('ready'))
      .catch((error: unknown) => {
        // setupPlayer rejects if a previous mount (fast refresh) already set it up.
        const message = error instanceof Error ? error.message : String(error);
        setPlayer(message.includes('already been initialized') ? 'ready' : 'failed');
      });
  }, []);

  return (
    <View style={styles.container}>
      <Stack.Screen options={{ title: 'Baptist Cloud' }} />
      <Text style={[styles.title, { color: colors.text }]}>Baptist Cloud</Text>
      <Text style={{ color: colors.text }} accessibilityLiveRegion="polite">
        Audio player: {player}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: 12 },
  title: { fontSize: 24, fontWeight: '600' },
});
