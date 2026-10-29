import { beforeEach, describe, expect, it, vi } from 'vitest';
import {
  initProductSheet,
  jumpTo,
  openProductSheet,
  readProductDetail,
  renderProductSheet,
} from '../../../src/product-sheet';
import { getBakePlan } from '../../../src/shop-status';
import { bakeScheduleOf, shopHoursOf } from '../../../src/status';
import { installMatchMedia, sampleTheme } from './helpers';

function productDom(): void {
  document.body.innerHTML = `
    <section id="season">
      <button class="season-card" type="button" data-product="croque">
        <span class="ph" data-label="bread-seasonal"><img src="croque.webp" alt="クロックの写真" width="900" height="678"></span>
        <span class="season-card__name">クロックムッシュ</span>
        <span class="season-card__excerpt">抜粋</span>
        <span data-product-copy><p>一枚目の全文</p><p>二枚目</p></span>
        <span class="price">999<small>円</small></span>
        <span class="tag tag--season">期間限定</span>
      </button>
    </section>
    <ul>
      <li data-category="meal">
        <button class="menu-card" type="button" data-product="focaccia">
          <span class="menu-card__name">フォカッチャ</span>
          <span class="price">480<small>円</small></span>
        </button>
      </li>
      <li data-category="drink">
        <button class="menu-card" type="button" data-product="coffee">
          <span class="menu-card__name">コーヒー</span>
        </button>
      </li>
    </ul>
    <template data-product-detail="focaccia">
      <h2 class="sheet__title">組み立て済み</h2>
      <div class="sheet__bake">古い焼き上がり</div>
      <div class="btn-row"><a href="#shop" data-jump>アクセスを見る</a></div>
    </template>
    <dialog id="product-sheet" aria-labelledby="sheet-title">
      <div data-sheet-panel>
        <div data-sheet-grip><span></span></div>
        <button type="button" data-close>閉じる</button>
        <div data-sheet-body></div>
      </div>
    </dialog>
    <section id="shop"></section>
    <section id="bake"></section>
  `;
}

describe('readProductDetail', () => {
  beforeEach(productDom);

  it('reads the card, the hidden copy, and the category on the parent', () => {
    const croque = readProductDetail('croque');
    expect(croque).toMatchObject({
      name: 'クロックムッシュ',
      line: 'seasonal',
      paragraphs: ['一枚目の全文', '二枚目'],
    });
    expect(croque?.image?.label).toBe('bread-seasonal');
    expect(readProductDetail('coffee')?.category).toBe('drink');
    expect(readProductDetail('missing')).toBeNull();
  });
});

describe('renderProductSheet', () => {
  beforeEach(productDom);

  it('marks the next bake and describes a drink without a bake time', () => {
    const theme = sampleTheme();
    theme.bakeSchedule = [
      ...theme.bakeSchedule,
      { time: '11:00', items: ['croque'] },
    ];
    const plan = getBakePlan(10 * 60 + 30, shopHoursOf(theme), bakeScheduleOf(theme));
    const croque = readProductDetail('croque');
    const body = document.querySelector<HTMLElement>('[data-sheet-body]');
    if (!croque || !body) throw new Error('missing product');
    renderProductSheet(body, croque, plan, shopHoursOf(theme));
    expect(body.querySelector('#sheet-title')?.textContent).toBe('クロックムッシュ');
    expect(body.querySelector('.tag')?.textContent).toContain('期間限定');
    expect(body.querySelector('.price')?.textContent).toContain('999');
    expect(body.querySelector('.sheet__text')?.textContent).toBe('一枚目の全文');
    expect(body.textContent).not.toContain('サンプル');
    expect(body.querySelector('.sheet__bake-times .is-next')?.textContent).toBe('11:00');
    expect(body.querySelector('img')?.getAttribute('loading')).toBeNull();
    expect(body.querySelector('[data-jump]')?.getAttribute('href')).toBe('#shop');

    const coffee = readProductDetail('coffee');
    if (!coffee) throw new Error('missing coffee');
    renderProductSheet(body, coffee, plan, shopHoursOf(theme));
    expect(body.querySelector('.sheet__bake-text')?.textContent).toBe(
      'カフェのご利用は10:00〜17:00です。焼きたてのパンと一緒にどうぞ。'
    );
  });
});

describe('openProductSheet', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2026, 9, 23, 10, 30, 0));
    window.bkeryTheme = sampleTheme();
    installMatchMedia(() => false);
    productDom();
    initProductSheet();
  });

  it('opens from a product button and uses a prebuilt detail when one exists', () => {
    document.querySelector<HTMLButtonElement>('[data-product="croque"]')?.click();
    const body = document.querySelector('[data-sheet-body]');
    expect(document.querySelector<HTMLDialogElement>('#product-sheet')?.open).toBe(true);
    expect(document.documentElement.classList.contains('is-locked')).toBe(true);
    expect(body?.textContent).toContain('クロックムッシュ');

    expect(openProductSheet('coffee')).toBe(false);
    expect(body?.textContent).toContain('クロックムッシュ');

    document.querySelector<HTMLButtonElement>('[data-close]')?.click();
  });

  it('replaces a prebuilt bake box with the live plan', () => {
    expect(openProductSheet('focaccia')).toBe(true);
    const body = document.querySelector('[data-sheet-body]');
    expect(body?.textContent).toContain('組み立て済み');
    expect(body?.textContent).not.toContain('古い焼き上がり');
    expect(body?.querySelector('.sheet__bake-times .is-next')?.textContent).toBe('11:00');
  });

  it('closes and then moves focus to the linked section', async () => {
    openProductSheet('croque');
    const shop = document.getElementById('shop');
    document.querySelector<HTMLAnchorElement>('[data-jump]')?.click();
    await vi.advanceTimersByTimeAsync(380);
    expect(document.querySelector<HTMLDialogElement>('#product-sheet')?.open).toBe(false);
    expect(document.documentElement.classList.contains('is-locked')).toBe(false);
    expect(shop?.getAttribute('tabindex')).toBe('-1');
    jumpTo('#bake');
    expect(document.getElementById('bake')?.getAttribute('tabindex')).toBe('-1');
  });

  it('returns false without a theme', () => {
    window.bkeryTheme = undefined;
    expect(openProductSheet('croque')).toBe(false);
  });
});
