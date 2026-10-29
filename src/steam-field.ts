/** 湯気の出しかたと、シェーダーが読むカラム位置。Three.js には依存しない。 */

export type SteamColumn = {
  x: number;
  width: number;
};

export type SteamSettings = {
  enabled: boolean;
  density: number;
  speed: number;
  column: number;
  background: number;
};

export type SteamMotion = {
  density: number;
  speed: number;
  column: number;
  background: number;
};

export const STEAM_DEFAULTS: SteamSettings = {
  enabled: true,
  density: 50,
  speed: 50,
  column: 100,
  background: 100,
};

export function clampSteamPercent(value: unknown, fallback: number): number {
  const number = typeof value === 'number' ? value : typeof value === 'string' && value.trim() !== '' ? Number(value) : Number.NaN;
  if (!Number.isFinite(number)) return fallback;
  return Math.min(100, Math.max(0, Math.round(number)));
}

/** カスタマイザー未設定のページは、今までどおり湯気を出す。 */
export function readSteamSettings(theme: { steam?: Partial<Record<keyof SteamSettings, unknown>> } | null | undefined): SteamSettings {
  const raw = theme?.steam;
  if (!raw) return { ...STEAM_DEFAULTS };
  const enabled = raw.enabled;
  return {
    enabled: enabled === true || enabled === 1 || enabled === '1',
    density: clampSteamPercent(raw.density, STEAM_DEFAULTS.density),
    speed: clampSteamPercent(raw.speed, STEAM_DEFAULTS.speed),
    column: clampSteamPercent(raw.column, STEAM_DEFAULTS.column),
    background: clampSteamPercent(raw.background, STEAM_DEFAULTS.background),
  };
}

/** 濃さ 50 が標準。速さ 50 は立ち上り 0.085。スライダー 100 が最大。 */
export function steamMotion(settings: SteamSettings): SteamMotion {
  return {
    density: clampSteamPercent(settings.density, STEAM_DEFAULTS.density) / 100,
    speed: (clampSteamPercent(settings.speed, STEAM_DEFAULTS.speed) / 100) * 0.17,
    column: (clampSteamPercent(settings.column, STEAM_DEFAULTS.column) / 100) * 1.28,
    background: (clampSteamPercent(settings.background, STEAM_DEFAULTS.background) / 100) * 0.8,
  };
}

export function shouldStartSteam(reducedMotion: boolean, reducedTransparency: boolean, enabled = true): boolean {
  return enabled && !reducedMotion && !reducedTransparency;
}

/** 420px カラムの画面上の位置。はみ出した分はビューポート内に収める。 */
export function steamColumn(
  rect: Pick<DOMRect, 'left' | 'width'> | null,
  viewportWidth: number,
): SteamColumn {
  const view = Number.isFinite(viewportWidth) ? Math.max(viewportWidth, 0) : 0;
  if (!rect || rect.width <= 0 || view <= 0) {
    return { x: 0, width: view };
  }
  const left = Math.min(Math.max(rect.left, 0), view);
  const right = Math.min(Math.max(rect.left + rect.width, 0), view);
  return { x: left, width: Math.max(right - left, 0) };
}

export const STEAM_VERTEX = `
varying vec2 vUv;
void main() {
  vUv = uv;
  gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
}
`;

export const STEAM_FRAGMENT = `
varying vec2 vUv;
uniform float uTime;
uniform vec2 uColumn;
uniform float uDensity;
uniform float uSpeed;
uniform float uColumnStrength;
uniform float uBackgroundStrength;

float hash(vec2 p) {
  p = fract(p * vec2(123.34, 345.45));
  p += dot(p, p + 34.345);
  return fract(p.x * p.y);
}

float noise(vec2 p) {
  vec2 i = floor(p);
  vec2 f = fract(p);
  float a = hash(i);
  float b = hash(i + vec2(1.0, 0.0));
  float c = hash(i + vec2(0.0, 1.0));
  float d = hash(i + vec2(1.0, 1.0));
  vec2 u = f * f * (3.0 - 2.0 * f);
  return mix(mix(a, b, u.x), mix(c, d, u.x), u.y);
}

float fbm(vec2 p) {
  float value = 0.0;
  float amplitude = 0.5;
  for (int i = 0; i < 5; i++) {
    value += amplitude * noise(p);
    p = p * 2.02 + vec2(1.7, 9.2);
    amplitude *= 0.5;
  }
  return value;
}

void main() {
  vec2 uv = vUv;
  float motion = uTime * uSpeed;
  float rise = motion;
  vec2 p = vec2(uv.x * 1.7, uv.y * 1.35 - rise);
  p.x += sin(p.y * 1.4 + motion * 2.588) * 0.09;
  float warp = fbm(p + vec2(0.0, motion * 0.353));
  float body = fbm(p + warp * 1.15);
  float threads = fbm(p * 1.8 + vec2(4.2, -rise * 1.4));
  float wisp = smoothstep(0.28, 0.7, body);
  float streak = smoothstep(0.4, 0.78, threads);
  float height = smoothstep(0.0, 0.1, uv.y) * smoothstep(1.0, 0.84, uv.y);
  float px = gl_FragCoord.x;
  float inside = smoothstep(uColumn.x - 28.0, uColumn.x + 16.0, px)
    * smoothstep(uColumn.x + uColumn.y + 28.0, uColumn.x + uColumn.y - 16.0, px);
  float lanes = 0.72 + 0.28 * sin(uv.x * 11.0 + warp * 2.4);
  float density = (wisp * 0.9 + streak) * height * lanes;
  density *= mix(uBackgroundStrength, uColumnStrength, inside);
  vec3 color = mix(vec3(0.9, 0.95, 0.99), vec3(1.0, 0.99, 0.96), inside);
  float alpha = clamp(density, 0.0, 1.0) * 0.62 * uDensity;
  gl_FragColor = vec4(color, alpha);
}
`;
