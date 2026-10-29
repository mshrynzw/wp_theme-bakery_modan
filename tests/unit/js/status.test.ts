import { beforeEach, describe, expect, it, vi } from 'vitest';
import { applyShopStatus, initStatus, statusRefreshDelay } from '../../../src/status';
import { installMatchMedia, sampleTheme } from './helpers';

function statusDom(): void {
  document.body.innerHTML = `
    <span data-status-dot></span>
    <span data-status-label>営業時間</span>
    <span data-status-label>予備</span>
    <p data-status-sub></p>
    <div data-bake-next></div>
    <ol>
      <li class="bake__item" data-bake-slot="10:00">
        <div class="chips"><button type="button" data-product="croissant">クロワッサン</button></div>
      </li>
      <li class="bake__item" data-bake-slot="11:00">
        <div class="chips">
          <button type="button" data-product="focaccia">フォカッチャ</button>
          <button type="button" data-product="croque">クロック</button>
        </div>
      </li>
      <li class="bake__item" data-bake-slot="15:00">
        <div class="chips">
          <button type="button" data-product="apple">アップルパイ</button>
          <button type="button" data-product="muffin">マフィン</button>
        </div>
      </li>
      <li class="bake__item" data-bake-slot="9:00"></li>
    </ol>
    <span data-next-bake-short></span>
    <small data-next-bake-quick>今日の時間を見る</small>
  `;
}

function text(selector: string): string {
  return document.querySelector(selector)?.textContent ?? '';
}

function slot(time: string): HTMLElement {
  const found = Array.from(document.querySelectorAll<HTMLElement>('[data-bake-slot]')).find((item) => item.dataset.bakeSlot === time);
  if (!found) throw new Error(`missing ${time}`);
  return found;
}

describe('statusRefreshDelay', () => {
  it('waits until the next minute plus a short margin', () => {
    expect(statusRefreshDelay(1000)).toBe(59050);
    expect(statusRefreshDelay(60000)).toBe(60050);
  });
});

describe('applyShopStatus', () => {
  beforeEach(statusDom);

  it('is open at the opening minute and marks the current bake as past', () => {
    applyShopStatus(10 * 60, 1, sampleTheme());
    expect(text('[data-status-label]')).toBe('営業中・18:00まで');
    expect(document.querySelectorAll('[data-status-label]')[1]?.textContent).toBe('営業中・18:00まで');
    expect(text('[data-status-sub]')).toBe('カフェのご利用は17:00まで');
    expect(document.querySelector('[data-status-dot]')?.getAttribute('data-state')).toBe('open');
    expect(slot('10:00').classList.contains('is-past')).toBe(true);
    expect(slot('10:00').classList.contains('is-next')).toBe(false);
    expect(slot('11:00').classList.contains('is-next')).toBe(true);
    expect(document.querySelector('[data-bake-next] small')?.textContent).toBe('あと1時間');
    expect(text('[data-next-bake-short]')).toBe('次の焼き上がり 11:00\u3000フォカッチャ ほか');
    expect(text('[data-next-bake-quick]')).toBe('次は11:00');
    expect(document.querySelector('[data-bake-next] .chips')?.textContent).toContain('フォカッチャ');
  });

  it('uses the mock phrase before opening instead of a countdown', () => {
    applyShopStatus(9 * 60, 1, sampleTheme());
    expect(text('[data-status-label]')).toBe('開店前・10:00から');
    expect(document.querySelector('[data-status-dot]')?.getAttribute('data-state')).toBe('soon');
    expect(slot('10:00').classList.contains('is-next')).toBe(true);
    expect(slot('10:00').classList.contains('is-past')).toBe(false);
    expect(document.querySelector('[data-bake-next] small')?.textContent).toBe('開店と同時に');
    expect(text('[data-next-bake-short]')).toBe('次の焼き上がり 10:00\u3000クロワッサン');
    expect(text('[data-next-bake-quick]')).toBe('次は10:00');
  });

  it('ends the cafe at the cafe closing minute and keeps bread sales open', () => {
    applyShopStatus(17 * 60, 1, sampleTheme());
    expect(text('[data-status-sub]')).toBe('カフェのご利用は終了しました（パンの販売は18:00まで）');
    expect(document.querySelector('[data-status-dot]')?.getAttribute('data-state')).toBe('open');
    expect(text('[data-bake-next]')).toBe('本日の焼き上がりはすべて終了しました');
    expect(text('[data-next-bake-short]')).toBe('');
    expect(text('[data-next-bake-quick]')).toBe('今日の時間を見る');
  });

  it('is closed at the closing minute', () => {
    applyShopStatus(18 * 60, 1, sampleTheme());
    expect(document.querySelector('[data-status-dot]')?.getAttribute('data-state')).toBe('closed');
    expect(text('[data-status-label]')).toBe('本日の営業は終了しました');
  });

  it('formats a one-hour wait from the shared helper', () => {
    applyShopStatus(14 * 60, 1, sampleTheme());
    expect(document.querySelector('[data-bake-next] small')?.textContent).toBe('あと1時間');
    expect(slot('15:00').classList.contains('is-next')).toBe(true);
    expect(text('[data-next-bake-short]')).toBe('次の焼き上がり 15:00\u3000アップルパイ ほか');
  });

  it('replaces the bake message on a closed weekday', () => {
    const theme = sampleTheme();
    theme.shop.closedWeekdays = [0];
    applyShopStatus(12 * 60, 0, theme);
    expect(text('[data-status-label]')).toBe('本日は定休日です');
    expect(text('[data-status-sub]')).toBe('営業時間 10:00〜18:00');
    expect(document.querySelector('[data-status-dot]')?.getAttribute('data-state')).toBe('holiday');
    expect(text('[data-bake-next]')).toBe('本日は定休日です');
    document.querySelectorAll('[data-bake-slot]').forEach((item) => {
      if (item.getAttribute('data-bake-slot') === '9:00') {
        expect(item.classList.contains('is-past')).toBe(false);
        return;
      }
      expect(item.classList.contains('is-past')).toBe(true);
    });
    expect(text('[data-next-bake-quick]')).toBe('今日の時間を見る');
  });

  it('accepts weekday numbers that arrived as strings', () => {
    const theme = sampleTheme();
    theme.shop.closedWeekdays = ['2'] as unknown as number[];
    applyShopStatus(12 * 60, 2, theme);
    expect(text('[data-status-label]')).toBe('本日は定休日です');
  });
});

describe('initStatus', () => {
  beforeEach(statusDom);

  it('returns without a theme', () => {
    initStatus();
    expect(text('[data-status-label]')).toBe('営業時間');
  });

  it('paints from the viewer clock and refreshes when the tab becomes visible', () => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2026, 9, 23, 9, 0, 0));
    window.bkeryTheme = sampleTheme();
    initStatus();
    expect(text('[data-status-label]')).toBe('開店前・10:00から');

    vi.setSystemTime(new Date(2026, 9, 23, 12, 0, 0));
    Object.defineProperty(document, 'hidden', { configurable: true, get: () => true });
    document.dispatchEvent(new Event('visibilitychange'));
    expect(text('[data-status-label]')).toBe('開店前・10:00から');

    Object.defineProperty(document, 'hidden', { configurable: true, get: () => false });
    document.dispatchEvent(new Event('visibilitychange'));
    expect(text('[data-status-label]')).toBe('営業中・18:00まで');
  });
});

describe('initStatus motion query', () => {
  it('does not require matchMedia', () => {
    installMatchMedia(() => false);
    window.bkeryTheme = sampleTheme();
    expect(() => initStatus()).not.toThrow();
  });
});
