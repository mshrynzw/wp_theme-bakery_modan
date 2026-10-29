import { beforeEach, describe, expect, it, vi } from 'vitest';
import { initHero } from '../../../src/hero';
import { installIntersectionObserver, installMatchMedia, sampleTheme } from './helpers';

function heroDom(): HTMLElement {
  document.body.innerHTML = `
    <div data-hero>
      <figure class="hero__slide ph is-active"><img src="a.webp" alt="一枚目"></figure>
      <figure class="hero__slide ph"><img src="b.webp" alt="二枚目"></figure>
      <figure class="hero__slide ph"><img src="c.webp" alt="三枚目"></figure>
    </div>
    <div data-hero-dots role="group"></div>
  `;
  const root = document.querySelector<HTMLElement>('[data-hero]');
  if (!root) throw new Error('missing hero');
  return root;
}

function active(): number {
  return Array.from(document.querySelectorAll('.hero__slide')).findIndex((slide) => slide.classList.contains('is-active'));
}

describe('initHero', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    window.bkeryTheme = sampleTheme();
    installMatchMedia((query) => query.includes('prefers-reduced-motion') ? false : false);
  });

  it('opens the door and builds dots even when bkeryTheme is missing', async () => {
    window.bkeryTheme = undefined;
    document.documentElement.classList.remove('is-loaded');
    heroDom();
    initHero();
    await vi.advanceTimersByTimeAsync(1200);
    expect(document.documentElement.classList.contains('is-loaded')).toBe(true);
    expect(document.querySelector('[data-hero-dots]')?.childElementCount).toBe(3);
  });

  it('returns when the door markup is missing', () => {
    document.body.innerHTML = '';
    document.documentElement.classList.remove('is-loaded');
    initHero();
    expect(document.documentElement.classList.contains('is-loaded')).toBe(false);
  });

  it('advances every 6 seconds, loads the door once, and lazy-loads later slides', async () => {
    heroDom();
    initHero();
    const images = document.querySelectorAll<HTMLImageElement>('.hero__slide img');
    expect(images[0]?.hasAttribute('loading')).toBe(false);
    expect(images[1]?.getAttribute('loading')).toBe('lazy');
    expect(images[1]?.decoding).toBe('async');
    expect(document.querySelector('[data-hero-dots] button')?.getAttribute('aria-label')).toBe('写真1を表示');

    await vi.advanceTimersByTimeAsync(5999);
    expect(active()).toBe(0);
    await vi.advanceTimersByTimeAsync(1);
    expect(active()).toBe(1);
    expect(document.querySelectorAll('[data-hero-dots] button')[1]?.getAttribute('aria-current')).toBe('true');
    expect(document.documentElement.classList.contains('is-loaded')).toBe(true);

    await vi.advanceTimersByTimeAsync(6000);
    expect(active()).toBe(2);
    expect(document.documentElement.classList.contains('is-loaded')).toBe(true);
  });

  it('stops while the pointer is a mouse hover and while the tab is hidden', async () => {
    const root = heroDom();
    initHero();
    root.dispatchEvent(new PointerEvent('pointerenter', { pointerType: 'mouse', bubbles: true }));
    await vi.advanceTimersByTimeAsync(6000);
    expect(active()).toBe(0);
    expect(document.querySelector('[data-hero-dots]')?.classList.contains('is-paused')).toBe(true);

    root.dispatchEvent(new PointerEvent('pointerleave', { pointerType: 'mouse', bubbles: true }));
    await vi.advanceTimersByTimeAsync(6000);
    expect(active()).toBe(1);

    Object.defineProperty(document, 'hidden', { configurable: true, get: () => true });
    document.dispatchEvent(new Event('visibilitychange'));
    await vi.advanceTimersByTimeAsync(6000);
    expect(active()).toBe(1);
    Object.defineProperty(document, 'hidden', { configurable: true, get: () => false });
  });

  it('ignores a hover that is not a mouse and a swipe of 40px', async () => {
    const root = heroDom();
    initHero();
    root.dispatchEvent(new PointerEvent('pointerenter', { pointerType: 'touch', bubbles: true }));
    root.dispatchEvent(new PointerEvent('pointerdown', { clientX: 100, clientY: 20, bubbles: true }));
    root.dispatchEvent(new PointerEvent('pointerup', { clientX: 60, clientY: 20, bubbles: true }));
    expect(active()).toBe(0);
    root.dispatchEvent(new PointerEvent('pointerdown', { clientX: 100, clientY: 20, bubbles: true }));
    root.dispatchEvent(new PointerEvent('pointerup', { clientX: 59, clientY: 20, bubbles: true }));
    expect(active()).toBe(1);
    await vi.advanceTimersByTimeAsync(6000);
    expect(active()).toBe(2);
  });

  it('shows the chosen dot and does not autoplay when motion is reduced', async () => {
    installMatchMedia((query) => query.includes('prefers-reduced-motion'));
    heroDom();
    initHero();
    const dots = document.querySelector('[data-hero-dots]');
    expect(dots?.classList.contains('is-static')).toBe(true);
    dots?.querySelectorAll('button')[2]?.dispatchEvent(new MouseEvent('click', { bubbles: true }));
    expect(active()).toBe(2);
    await vi.advanceTimersByTimeAsync(12000);
    expect(active()).toBe(2);
  });

  it('pauses when the door leaves the viewport', async () => {
    const io = installIntersectionObserver();
    const root = heroDom();
    initHero();
    io.intersect(0, root, { isIntersecting: false, intersectionRatio: 0 });
    await vi.advanceTimersByTimeAsync(6000);
    expect(active()).toBe(0);
  });
});
