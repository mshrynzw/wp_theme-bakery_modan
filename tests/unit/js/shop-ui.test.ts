import { beforeEach, describe, expect, it, vi } from 'vitest';
import { watchImages } from '../../../src/dom';
import { initMenuTabs, initShopUi, showToast } from '../../../src/shop-ui';
import { installIntersectionObserver, installMatchMedia, sampleTheme } from './helpers';

function shopDom(): void {
  document.body.innerHTML = `
    <a href="#" data-link="map">地図</a>
    <a href="#keep" data-link="calendar">カレンダー</a>
    <span data-insta-handle>未設定</span>
    <button type="button" data-copy-address>コピー</button>
    <div data-toast></div>
    <section id="hero" data-section="hero"></section>
    <section id="concept" data-section="concept"></section>
    <nav>
      <a href="#hero" data-nav="hero">トップ</a>
      <a href="#concept" data-nav="concept">コンセプト</a>
    </nav>
    <nav data-dock>
      <a href="#menu" data-nav="menu">メニュー</a>
    </nav>
    <div data-menu-tabs>
      <button type="button" data-category="all" aria-pressed="true">すべて</button>
      <button type="button" data-category="sweet" aria-pressed="false">甘いパン</button>
    </div>
    <ul class="menu__grid">
      <li data-category="meal"><button type="button">食事</button></li>
      <li data-category="sweet"><button type="button">甘い</button></li>
    </ul>
    <span class="ph" data-label="lost"><img alt=""></span>
  `;
}

describe('watchImages', () => {
  it('marks a loaded image and a missing image', () => {
    document.body.innerHTML = `
      <span class="ph"><img id="ok" alt=""></span>
      <span class="ph" data-label="gone"><img id="bad" alt=""></span>
      <span class="ph"><img id="later" alt=""></span>
    `;
    const ok = document.getElementById('ok');
    const bad = document.getElementById('bad');
    const later = document.getElementById('later');
    if (!(ok instanceof HTMLImageElement) || !(bad instanceof HTMLImageElement) || !(later instanceof HTMLImageElement)) {
      throw new Error('missing image');
    }
    Object.defineProperty(ok, 'complete', { configurable: true, get: () => true });
    Object.defineProperty(ok, 'naturalWidth', { configurable: true, get: () => 20 });
    Object.defineProperty(bad, 'complete', { configurable: true, get: () => true });
    Object.defineProperty(bad, 'naturalWidth', { configurable: true, get: () => 0 });
    Object.defineProperty(later, 'complete', { configurable: true, get: () => false });
    watchImages(document);
    expect(ok.classList.contains('is-loaded')).toBe(true);
    expect(bad.closest('.ph')?.classList.contains('is-missing')).toBe(true);
    later.dispatchEvent(new Event('load'));
    expect(later.classList.contains('is-loaded')).toBe(true);
  });
});

describe('initShopUi', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    installMatchMedia(() => false);
    shopDom();
  });

  it('returns without a theme', () => {
    initShopUi();
    expect(document.querySelector('[data-link="map"]')?.getAttribute('href')).toBe('#');
  });

  it('fills links and the instagram handle, and toasts a copied address', async () => {
    const writeText = vi.fn().mockResolvedValue(undefined);
    Object.defineProperty(navigator, 'clipboard', { configurable: true, value: { writeText } });
    const theme = sampleTheme();
    theme.links.calendar = '';
    window.bkeryTheme = theme;
    initShopUi();
    expect(document.querySelector<HTMLAnchorElement>('[data-link="map"]')?.href).toContain('https://maps.example/bakery');
    expect(document.querySelector('[data-link="calendar"]')?.getAttribute('href')).toBe('#keep');
    expect(document.querySelector('[data-insta-handle]')?.textContent).toBe('@bakery_s');

    document.querySelector<HTMLButtonElement>('[data-copy-address]')?.click();
    await Promise.resolve();
    await Promise.resolve();
    expect(writeText).toHaveBeenCalledWith('〒123-4567 北海道札幌市');
    const toast = document.querySelector('[data-toast]');
    expect(toast?.textContent).toBe('住所をコピーしました');
    expect(toast?.classList.contains('is-visible')).toBe(true);
    await vi.advanceTimersByTimeAsync(2200);
    expect(toast?.classList.contains('is-visible')).toBe(false);
  });

  it('toasts a failure when the clipboard rejects the copy', async () => {
    Object.defineProperty(navigator, 'clipboard', {
      configurable: true,
      value: { writeText: vi.fn().mockRejectedValue(new Error('nope')) },
    });
    document.execCommand = () => false;
    window.bkeryTheme = sampleTheme();
    initShopUi();
    document.querySelector<HTMLButtonElement>('[data-copy-address]')?.click();
    await Promise.resolve();
    await Promise.resolve();
    expect(document.querySelector('[data-toast]')?.textContent).toBe(
      'コピーできませんでした。住所を長押しして選択してください'
    );
  });

  it('filters menu items and removes the entering class when the animation ends', () => {
    window.bkeryTheme = sampleTheme();
    initMenuTabs();
    const sweet = document.querySelector<HTMLButtonElement>('[data-category="sweet"]');
    sweet?.click();
    const items = document.querySelectorAll<HTMLElement>('.menu__grid li');
    expect(items[0]?.hidden).toBe(true);
    expect(items[1]?.hidden).toBe(false);
    expect(items[1]?.classList.contains('is-entering')).toBe(true);
    expect(items[1]?.style.getPropertyValue('--i')).toBe('0');
    expect(sweet?.getAttribute('aria-pressed')).toBe('true');
    items[1]?.dispatchEvent(new Event('animationend', { bubbles: true }));
    expect(items[1]?.classList.contains('is-entering')).toBe(false);

    sweet?.click();
    expect(items[0]?.hidden).toBe(true);
  });

  it('marks the current section and shows the dock after the hero', () => {
    window.bkeryTheme = sampleTheme();
    const io = installIntersectionObserver();
    initShopUi();
    expect(document.querySelector('[data-nav="hero"]')?.getAttribute('aria-current')).toBe('true');
    expect(io.observers[0]?.options?.rootMargin).toBe('-45% 0px -50% 0px');
    expect(io.observers[1]?.options?.threshold).toEqual([0, 0.2, 0.4, 0.6, 0.8, 1]);
    const dock = document.querySelector('[data-dock]');
    expect(dock?.hasAttribute('inert') || (dock instanceof HTMLElement && dock.inert)).toBe(true);

    const concept = document.getElementById('concept');
    const hero = document.getElementById('hero');
    if (!concept || !hero) throw new Error('missing section');
    io.intersect(0, concept);
    expect(document.querySelector('[data-nav="concept"]')?.getAttribute('aria-current')).toBe('true');
    expect(document.querySelector('[data-nav="hero"]')?.hasAttribute('aria-current')).toBe(false);

    io.intersect(1, hero, { isIntersecting: true, intersectionRatio: 0.4 });
    expect(dock?.classList.contains('is-visible')).toBe(false);
    io.intersect(1, hero, { isIntersecting: true, intersectionRatio: 0.39 });
    expect(dock?.classList.contains('is-visible')).toBe(true);
    expect(dock instanceof HTMLElement && dock.inert).toBe(false);
  });

  it('hides the entering animation when motion is reduced', () => {
    installMatchMedia((query) => query.includes('prefers-reduced-motion'));
    window.bkeryTheme = sampleTheme();
    initMenuTabs();
    document.querySelector<HTMLButtonElement>('[data-category="sweet"]')?.click();
    expect(document.querySelector('.menu__grid li:last-child')?.classList.contains('is-entering')).toBe(false);
  });
});

describe('showToast', () => {
  it('does nothing when the toast node is missing', () => {
    expect(() => showToast('住所をコピーしました')).not.toThrow();
  });
});
