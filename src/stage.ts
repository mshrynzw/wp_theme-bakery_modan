import { prefersReducedMotion } from './dom';

export const STAGE_INTERVAL_MS = 7000;
const WIDE_QUERY = '(min-width: 768px)';
const CAPTION_MS = 320;

type StageState = {
  front: number;
  current: string | null;
  wanted: string;
  token: number;
  cycle?: number;
  captionTimer?: number;
};

const states = new WeakMap<HTMLElement, StageState>();

function isWide(): boolean {
  return window.matchMedia?.(WIDE_QUERY).matches ?? false;
}

function imageList(image: string[] | undefined): string[] {
  if (!Array.isArray(image)) return [];
  return image.filter((src) => typeof src === 'string' && src.trim() !== '');
}

function preload(src: string): Promise<void> {
  return new Promise((resolve) => {
    const image = new Image();
    image.decoding = 'async';
    image.onload = () => resolve();
    image.onerror = () => resolve();
    image.src = src;
  });
}

function stopCycle(state: StageState): void {
  if (state.cycle !== undefined) window.clearInterval(state.cycle);
  state.cycle = undefined;
  state.token += 1;
}

function sceneText(id: string, scene: BkeryThemeData['scenes'][string]): string {
  if (scene.text) return scene.text;
  if (id !== 'instagram') return '';
  const handle = window.bkeryTheme?.links.instagramHandle ?? '';
  if (!handle) return '';
  return `${handle}\n季節限定パンや焼き上がりのようすを発信中`;
}

function updateCaption(el: HTMLElement, state: StageState, id: string, scene: BkeryThemeData['scenes'][string]): void {
  const caption = el.querySelector('.stage__caption');
  const section = el.querySelector('[data-stage-section]');
  const title = el.querySelector('[data-stage-title]');
  const text = el.querySelector('[data-stage-text]');
  if (state.captionTimer !== undefined) window.clearTimeout(state.captionTimer);
  if (caption instanceof HTMLElement) caption.classList.add('is-changing');
  state.captionTimer = window.setTimeout(() => {
    if (section) section.textContent = scene.section;
    if (title) title.textContent = scene.title;
    if (text) text.textContent = sceneText(id, scene);
    if (caption instanceof HTMLElement) caption.classList.remove('is-changing');
  }, prefersReducedMotion() ? 0 : CAPTION_MS);
}

async function showImage(el: HTMLElement, state: StageState, src: string): Promise<void> {
  const token = ++state.token;
  await preload(src);
  if (token !== state.token || !el.isConnected || !isWide()) return;
  const layers = Array.from(el.querySelectorAll<HTMLElement>('.stage__layer'));
  if (layers.length < 2) return;
  const backIndex = 1 - state.front;
  const back = layers[backIndex];
  const front = layers[state.front];
  const img = back?.querySelector('img');
  if (!back || !front || !(img instanceof HTMLImageElement)) return;
  img.src = src;
  back.classList.add('is-active');
  front.classList.remove('is-active');
  state.front = backIndex;
}

function apply(el: HTMLElement, state: StageState, id: string): void {
  const scene = window.bkeryTheme?.scenes?.[id];
  if (!scene || id === state.current || !isWide() || !el.isConnected) return;
  state.current = id;
  stopCycle(state);
  updateCaption(el, state, id, scene);
  const images = imageList(scene.image);
  if (!images.length) return;
  void showImage(el, state, images[0]);
  if (images.length > 1 && !prefersReducedMotion()) {
    let cursor = 0;
    state.cycle = window.setInterval(() => {
      if (!el.isConnected || !isWide()) {
        stopCycle(state);
        return;
      }
      cursor = (cursor + 1) % images.length;
      void showImage(el, state, images[cursor]);
    }, STAGE_INTERVAL_MS);
  }
}

export function setStageScene(id: string): void {
  const el = document.querySelector('[data-stage]');
  if (!(el instanceof HTMLElement)) return;
  const state = states.get(el);
  if (!state) return;
  state.wanted = id;
  if (isWide()) apply(el, state, id);
}

export function initStage(): void {
  if (!window.bkeryTheme) return;
  const el = document.querySelector('[data-stage]');
  if (!(el instanceof HTMLElement) || states.has(el)) return;
  const state: StageState = { front: 0, current: null, wanted: 'hero', token: 0 };
  states.set(el, state);
  const wide = window.matchMedia?.(WIDE_QUERY);
  wide?.addEventListener?.('change', () => {
    if (isWide()) {
      state.current = null;
      apply(el, state, state.wanted);
    } else {
      stopCycle(state);
    }
  });
  if (isWide()) apply(el, state, state.wanted);
}
