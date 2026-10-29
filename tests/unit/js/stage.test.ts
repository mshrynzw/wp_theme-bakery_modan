import { beforeEach, describe, expect, it, vi } from 'vitest';
import { initStage, setStageScene, STAGE_INTERVAL_MS } from '../../../src/stage';
import { installMatchMedia, sampleTheme } from './helpers';

class ImmediateImage {
  onload: (() => void) | null = null;
  onerror: (() => void) | null = null;
  decoding = '';
  set src(_value: string) {
    this.onload?.();
  }
}

function stageDom(): void {
  document.body.innerHTML = `
    <div class="stage" data-stage>
      <div class="stage__layer"><img alt=""></div>
      <div class="stage__layer"><img alt=""></div>
      <div class="stage__caption">
        <p data-stage-section></p>
        <p data-stage-title></p>
        <p data-stage-text></p>
      </div>
    </div>
  `;
}

function images(): HTMLImageElement[] {
  return Array.from(document.querySelectorAll<HTMLImageElement>('.stage__layer img'));
}

describe('initStage', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.stubGlobal('Image', ImmediateImage);
    window.bkeryTheme = sampleTheme();
    stageDom();
  });

  it('does not request a background image below 768px, then loads the wanted scene', async () => {
    let wide = false;
    const media = installMatchMedia((query) => (query.includes('768') ? wide : false));
    initStage();
    setStageScene('gallery');
    await vi.advanceTimersByTimeAsync(400);
    expect(images()[0]?.getAttribute('src')).toBeNull();
    expect(images()[1]?.getAttribute('src')).toBeNull();
    expect(document.querySelector('[data-stage-title]')?.textContent).toBe('');

    wide = true;
    media.emit('(min-width: 768px)');
    await vi.advanceTimersByTimeAsync(320);
    expect(images().some((img) => img.getAttribute('src') === 'https://example.com/a.webp')).toBe(true);
    expect(document.querySelector('[data-stage-title]')?.textContent).toBe('店内の\nようす');
    expect(document.querySelector('.stage__caption')?.classList.contains('is-changing')).toBe(false);
  });

  it('cycles multiple photos every 7 seconds and keeps a single photo still when motion is reduced', async () => {
    const interval = vi.spyOn(window, 'setInterval');
    installMatchMedia((query) => query.includes('768'));
    initStage();
    await vi.advanceTimersByTimeAsync(320);
    expect(interval).not.toHaveBeenCalledWith(expect.any(Function), STAGE_INTERVAL_MS);

    setStageScene('gallery');
    expect(interval).toHaveBeenCalledWith(expect.any(Function), STAGE_INTERVAL_MS);
    await vi.advanceTimersByTimeAsync(STAGE_INTERVAL_MS);
    expect(images().some((img) => img.getAttribute('src') === 'https://example.com/b.webp')).toBe(true);

    document.body.innerHTML = '';
    stageDom();
    installMatchMedia((query) => query.includes('768') || query.includes('prefers-reduced-motion'));
    interval.mockClear();
    initStage();
    setStageScene('gallery');
    expect(interval).not.toHaveBeenCalledWith(expect.any(Function), STAGE_INTERVAL_MS);
    await vi.advanceTimersByTimeAsync(0);
    expect(document.querySelector('[data-stage-section]')?.textContent).toBe('店内のようす');
  });

  it('fills an empty instagram caption from the handle and skips a scene that is already showing', async () => {
    installMatchMedia((query) => query.includes('768'));
    const interval = vi.spyOn(window, 'setInterval');
    initStage();
    setStageScene('instagram');
    await vi.advanceTimersByTimeAsync(320);
    expect(document.querySelector('[data-stage-text]')?.textContent).toBe(
      '@bakery_s\n季節限定パンや焼き上がりのようすを発信中'
    );
    expect(document.querySelector('.stage__caption')?.classList.contains('is-changing')).toBe(false);
    const calls = interval.mock.calls.filter((call) => call[1] === STAGE_INTERVAL_MS).length;
    setStageScene('instagram');
    expect(document.querySelector('.stage__caption')?.classList.contains('is-changing')).toBe(false);
    expect(interval.mock.calls.filter((call) => call[1] === STAGE_INTERVAL_MS).length).toBe(calls);
  });
});
