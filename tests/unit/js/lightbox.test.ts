import { beforeEach, describe, expect, it, vi } from 'vitest';
import { initLightbox, lightboxCaption } from '../../../src/lightbox';
import { installMatchMedia, sampleTheme } from './helpers';

function lightboxDom(): void {
  document.body.innerHTML = `
    <button type="button" data-lightbox>
      <img src="one.webp" alt="一枚目">
      <span class="gallery__caption">パンの棚</span>
    </button>
    <button type="button" data-lightbox>
      <img src="two.webp" alt="二枚目">
      <span class="gallery__caption">カウンター</span>
    </button>
    <dialog id="lightbox">
      <div class="lightbox__inner">
        <button type="button" data-close>閉じる</button>
        <figure>
          <img data-lightbox-img alt="">
          <figcaption data-lightbox-caption></figcaption>
        </figure>
        <button type="button" data-lightbox-prev></button>
        <button type="button" data-lightbox-next></button>
      </div>
    </dialog>
  `;
}

describe('lightboxCaption', () => {
  it('adds the position after the caption', () => {
    expect(lightboxCaption('パンの棚', 0, 2)).toBe('パンの棚（1 / 2）');
    expect(lightboxCaption('', 1, 2)).toBe('（2 / 2）');
  });
});

describe('initLightbox', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    window.bkeryTheme = sampleTheme();
    installMatchMedia(() => false);
    lightboxDom();
    initLightbox();
  });

  it('opens the chosen photo and steps with the buttons', async () => {
    document.querySelector<HTMLButtonElement>('[data-lightbox]')?.click();
    const img = document.querySelector<HTMLImageElement>('[data-lightbox-img]');
    expect(document.querySelector<HTMLDialogElement>('#lightbox')?.open).toBe(true);
    expect(img?.getAttribute('src')).toBe('one.webp');
    expect(img?.alt).toBe('一枚目');
    expect(document.querySelector('[data-lightbox-caption]')?.textContent).toBe('パンの棚（1 / 2）');

    document.querySelector<HTMLButtonElement>('[data-lightbox-next]')?.click();
    expect(img?.classList.contains('is-swapping')).toBe(true);
    expect(img?.getAttribute('src')).toBe('one.webp');
    await vi.advanceTimersByTimeAsync(160);
    expect(img?.getAttribute('src')).toBe('two.webp');
    expect(img?.classList.contains('is-swapping')).toBe(false);
    expect(document.querySelector('[data-lightbox-caption]')?.textContent).toBe('カウンター（2 / 2）');

    document.querySelector<HTMLButtonElement>('[data-lightbox-prev]')?.click();
    await vi.advanceTimersByTimeAsync(160);
    expect(img?.getAttribute('src')).toBe('one.webp');
  });

  it('wraps with the arrow keys and a swipe wider than 50px', async () => {
    document.querySelectorAll<HTMLButtonElement>('[data-lightbox]')[1]?.click();
    const box = document.querySelector<HTMLDialogElement>('#lightbox');
    box?.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true }));
    await vi.advanceTimersByTimeAsync(160);
    expect(document.querySelector('[data-lightbox-img]')?.getAttribute('src')).toBe('one.webp');

    const inner = document.querySelector('.lightbox__inner');
    inner?.dispatchEvent(new PointerEvent('pointerdown', { bubbles: true, clientX: 80 }));
    inner?.dispatchEvent(new PointerEvent('pointerup', { bubbles: true, clientX: 30 }));
    expect(document.querySelector('[data-lightbox-img]')?.classList.contains('is-swapping')).toBe(false);
    inner?.dispatchEvent(new PointerEvent('pointerdown', { bubbles: true, clientX: 80 }));
    inner?.dispatchEvent(new PointerEvent('pointerup', { bubbles: true, clientX: 29 }));
    await vi.advanceTimersByTimeAsync(160);
    expect(document.querySelector('[data-lightbox-img]')?.getAttribute('src')).toBe('two.webp');
  });

  it('changes the photo immediately when motion is reduced', () => {
    installMatchMedia((query) => query.includes('prefers-reduced-motion'));
    document.querySelector<HTMLButtonElement>('[data-lightbox]')?.click();
    document.querySelector<HTMLButtonElement>('[data-lightbox-next]')?.click();
    const img = document.querySelector('[data-lightbox-img]');
    expect(img?.getAttribute('src')).toBe('two.webp');
    expect(img?.classList.contains('is-swapping')).toBe(false);
  });
});
