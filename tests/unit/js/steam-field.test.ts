import { afterEach, describe, expect, it } from 'vitest';
import { initSteam, stopSteam } from '../../../src/steam';
import { readSteamSettings, STEAM_FRAGMENT, shouldStartSteam, steamColumn, steamMotion } from '../../../src/steam-field';
import { installMatchMedia, sampleTheme } from './helpers';

afterEach(() => {
  stopSteam();
});

describe('shouldStartSteam', () => {
  it('starts only when motion and transparency are both allowed', () => {
    expect(shouldStartSteam(false, false)).toBe(true);
    expect(shouldStartSteam(true, false)).toBe(false);
    expect(shouldStartSteam(false, true)).toBe(false);
    expect(shouldStartSteam(false, false, false)).toBe(false);
  });
});

describe('readSteamSettings', () => {
  it('keeps the current look when the customizer has not sent steam', () => {
    expect(readSteamSettings(undefined).enabled).toBe(true);
    const motion = steamMotion(readSteamSettings(sampleTheme()));
    expect(motion.density).toBe(0.5);
    expect(motion.speed).toBeCloseTo(0.085, 5);
    expect(motion.column).toBeCloseTo(1.28, 5);
    expect(motion.background).toBeCloseTo(0.8, 5);
  });

  it('turns steam off and clamps the sliders', () => {
    const settings = readSteamSettings({
      steam: { enabled: 0, density: 140, speed: -3, column: '40', background: 'nope' },
    });
    expect(settings.enabled).toBe(false);
    const motion = steamMotion({ ...settings, enabled: true });
    expect(motion.density).toBe(1);
    expect(motion.speed).toBe(0);
    expect(motion.column).toBeCloseTo(0.512, 5);
    expect(motion.background).toBeCloseTo(0.8, 5);
  });
});

describe('steamColumn', () => {
  it('uses the full viewport when the column is missing', () => {
    expect(steamColumn(null, 390)).toEqual({ x: 0, width: 390 });
  });

  it('keeps a column that sits inside the viewport', () => {
    expect(steamColumn({ left: 120, width: 420 }, 1440)).toEqual({ x: 120, width: 420 });
  });

  it('clips a column that crosses the viewport edge', () => {
    expect(steamColumn({ left: -40, width: 200 }, 390)).toEqual({ x: 0, width: 160 });
    expect(steamColumn({ left: 360, width: 420 }, 390)).toEqual({ x: 360, width: 30 });
  });
});

describe('STEAM_FRAGMENT', () => {
  it('rises over time and knows the column', () => {
    expect(STEAM_FRAGMENT).toContain('uniform float uTime');
    expect(STEAM_FRAGMENT).toContain('uniform vec2 uColumn');
    expect(STEAM_FRAGMENT).toContain('uniform float uDensity');
    expect(STEAM_FRAGMENT).toContain('uniform float uSpeed');
    expect(STEAM_FRAGMENT).toContain('uv.y * 1.35 - rise');
  });
});

describe('initSteam', () => {
  it('does not add a canvas when the customizer turns steam off', () => {
    installMatchMedia(() => false);
    window.bkeryTheme = { ...sampleTheme(), steam: { enabled: 0, density: 100, speed: 50, column: 100, background: 100 } };
    document.body.innerHTML = '<div class="site"></div>';
    initSteam();
    expect(document.querySelector('.steam')).toBeNull();
    delete window.bkeryTheme;
  });

  it('does not add a canvas when motion is reduced', () => {
    installMatchMedia((query) => query.includes('prefers-reduced-motion'));
    document.body.innerHTML = '<div class="site"></div>';
    initSteam();
    expect(document.querySelector('.steam')).toBeNull();
  });

  it('removes the canvas when WebGL is unavailable', async () => {
    installMatchMedia(() => false);
    document.body.innerHTML = '<div class="site"></div>';
    await initSteam();
    expect(document.querySelector('.steam')).toBeNull();
  });
});
