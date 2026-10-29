import {
  prefersReducedMotion,
  prefersReducedTransparency,
  watchReducedMotion,
  watchReducedTransparency,
} from './dom';
import { readSteamSettings, shouldStartSteam, steamMotion, type SteamSettings } from './steam-field';

let stopCurrent: (() => void) | null = null;
let loading: Promise<void> | null = null;
let preferencesBound = false;

function currentSettings(doc: Document): SteamSettings {
  return readSteamSettings((doc.defaultView ?? window).bkeryTheme);
}

/** 焼き上がりの湯気。操作は通したまま、カラムと背景の両方に薄く重ねる。 */
export function initSteam(doc: Document = document): Promise<void> {
  bindPreferences(doc);
  const settings = currentSettings(doc);
  if (!shouldStartSteam(prefersReducedMotion(), prefersReducedTransparency(), settings.enabled)) {
    stopSteam();
    return Promise.resolve();
  }
  if (stopCurrent) return Promise.resolve();
  if (loading) return loading;
  loading = import('./steam-gl')
    .then((mod) => {
      const next = currentSettings(doc);
      if (!shouldStartSteam(prefersReducedMotion(), prefersReducedTransparency(), next.enabled) || stopCurrent) return;
      const canvas = doc.createElement('canvas');
      canvas.className = 'steam';
      canvas.setAttribute('aria-hidden', 'true');
      doc.body.append(canvas);
      try {
        stopCurrent = mod.mountSteam(canvas, steamMotion(next));
      } catch {
        canvas.remove();
        stopCurrent = null;
      }
    })
    .then(() => undefined, () => undefined)
    .finally(() => {
      loading = null;
    });
  return loading;
}

export function stopSteam(): void {
  stopCurrent?.();
  stopCurrent = null;
}

function bindPreferences(doc: Document): void {
  if (preferencesBound) return;
  preferencesBound = true;
  const sync = () => {
    void initSteam(doc);
  };
  watchReducedMotion(sync);
  watchReducedTransparency(sync);
}
