import { closestElement, icon, prefersReducedMotion, watchImages } from './dom';
import { closeDialog, openDialog } from './dialog';
import { getBakePlan, isClosedWeekday, type BakePlan, type ShopHours } from './shop-status';
import { bakeScheduleOf, closedWeekdayList, shopHoursOf } from './status';

export type ProductImage = {
  src: string;
  alt: string;
  width: string;
  height: string;
  label: string;
};

export type ProductView = {
  id: string;
  name: string;
  paragraphs: string[];
  image: ProductImage | null;
  price: HTMLElement | null;
  tag: HTMLElement | null;
  line: 'seasonal' | 'regular';
  category: string;
};

/**
 * 商品本文は bkeryTheme に入らない。
 * カード（.season-card / .menu-card）と、任意の [data-product-detail="{id}"] から読む。
 * 詳細ノードに .sheet__title があるときは、その子をシートへ移す。
 */

function fileLabel(src: string): string {
  const base = src.split('/').pop() ?? src;
  const path = base.split('?')[0] ?? base;
  return path.replace(/\.[a-z0-9]+$/i, '');
}

function nodesFor(id: string, scope: ParentNode): HTMLElement[] {
  return Array.from(scope.querySelectorAll<HTMLElement>('[data-product]')).filter((el) => el.dataset.product === id);
}

function detailContent(id: string, scope: ParentNode): ParentNode | null {
  const found = Array.from(scope.querySelectorAll<HTMLElement>('[data-product-detail]')).find((el) => el.dataset.productDetail === id);
  if (!found) return null;
  if (found instanceof HTMLTemplateElement) return found.content;
  return found;
}

function readLine(source: HTMLElement | null): 'seasonal' | 'regular' {
  if (source?.dataset.line === 'seasonal' || source?.dataset.line === 'regular') return source.dataset.line;
  if (source?.classList.contains('season-card')) return 'seasonal';
  if (source?.querySelector('.tag--season')) return 'seasonal';
  if (source?.closest('#season')) return 'seasonal';
  return 'regular';
}

function categoryOf(element: HTMLElement | null | undefined): string {
  if (!element) return '';
  return element.dataset.category || element.dataset.cat || '';
}

function readCategory(source: HTMLElement | null): string {
  const own = categoryOf(source);
  if (own) return own;
  const holder = source?.closest<HTMLElement>('[data-category], [data-cat]');
  if (!holder || holder === source) return '';
  return categoryOf(holder);
}

function readParagraphs(source: HTMLElement | null, detail: ParentNode | null): string[] {
  const buckets: ParentNode[] = [];
  if (detail && !detail.querySelector('.sheet__title')) buckets.push(detail);
  const copy = source?.querySelector('[data-product-copy], .product-copy');
  if (copy) buckets.push(copy);
  const texts: string[] = [];
  buckets.forEach((bucket) => {
    const nodes = Array.from(bucket.querySelectorAll('p, li'));
    if (nodes.length) {
      nodes.forEach((node) => {
        const text = node.textContent?.trim();
        if (text) texts.push(text);
      });
      return;
    }
    if (bucket instanceof HTMLElement) {
      const text = bucket.textContent?.trim();
      if (text) texts.push(text);
    }
  });
  if (texts.length) return texts;
  const excerpt = source?.querySelector('.season-card__excerpt, .menu-card__excerpt');
  const excerptText = excerpt?.textContent?.trim();
  return excerptText ? [excerptText] : [];
}

function readImage(source: HTMLElement | null, detail: ParentNode | null): ProductImage | null {
  const image = source?.querySelector('img') ?? detail?.querySelector('img') ?? null;
  if (!(image instanceof HTMLImageElement)) return null;
  const src = image.getAttribute('src') ?? '';
  if (!src) return null;
  const frame = image.closest('.ph');
  const label = frame instanceof HTMLElement && frame.dataset.label ? frame.dataset.label : fileLabel(src);
  return {
    src,
    alt: image.alt,
    width: image.getAttribute('width') ?? '',
    height: image.getAttribute('height') ?? '',
    label,
  };
}

function defaultTag(line: 'seasonal' | 'regular'): HTMLElement {
  const tag = document.createElement('span');
  if (line === 'seasonal') {
    tag.className = 'tag tag--season';
    tag.append(icon('i-leaf'), document.createTextNode('期間限定'));
    return tag;
  }
  tag.className = 'tag tag--regular';
  tag.textContent = '定番';
  return tag;
}

export function readProductDetail(id: string, scope: ParentNode = document): ProductView | null {
  if (!id) return null;
  const nodes = nodesFor(id, scope);
  const detailHost = Array.from(scope.querySelectorAll<HTMLElement>('[data-product-detail]')).find((el) => el.dataset.productDetail === id) ?? null;
  const detail = detailContent(id, scope);
  const source = nodes.find((el) => el.matches('.season-card, .menu-card')) ?? nodes[0] ?? null;
  if (!source && !detail) return null;
  const line = readLine(source);
  const nameNode = source?.querySelector('.season-card__name, .menu-card__name, .sheet__title');
  let name = nameNode?.textContent?.trim() ?? '';
  if (!name && detail) name = detail.querySelector('.sheet__title')?.textContent?.trim() ?? '';
  if (!name && source && !source.matches('.season-card, .menu-card')) {
    name = source.textContent?.replace(/\s+/g, ' ').trim() ?? '';
  }
  const paragraphs = readParagraphs(source, detail);
  const image = readImage(source, detail);
  if (!name && !image && paragraphs.length === 0) return null;
  const priceNode = source?.querySelector('.price') ?? detail?.querySelector('.price') ?? null;
  const price = priceNode instanceof HTMLElement && priceNode.textContent?.trim()
    ? priceNode.cloneNode(true) as HTMLElement
    : null;
  const tagNode = source?.querySelector('.tag') ?? detail?.querySelector('.tag') ?? null;
  const tag = tagNode instanceof HTMLElement ? tagNode.cloneNode(true) as HTMLElement : defaultTag(line);
  return {
    id,
    name,
    paragraphs,
    image,
    price,
    tag,
    line,
    category: readCategory(source) || categoryOf(detailHost),
  };
}

function jumpLink(kind: 'shop' | 'bake'): HTMLAnchorElement {
  const link = document.createElement('a');
  link.className = kind === 'shop' ? 'btn btn--solid' : 'btn btn--line';
  link.href = kind === 'shop' ? '#shop' : '#bake';
  link.setAttribute('data-jump', '');
  link.append(icon(kind === 'shop' ? 'i-pin' : 'i-clock'), document.createTextNode(kind === 'shop' ? 'アクセスを見る' : '焼き上がり時間'));
  return link;
}

function bakeSectionFor(product: ProductView, plan: BakePlan, shop: ShopHours): HTMLElement | null {
  const section = document.createElement('div');
  section.className = 'sheet__bake';
  const mine = plan.slots.flatMap((slot, index) => (slot.items.includes(product.id) ? [{ slot, index }] : []));
  if (mine.length) {
    const title = document.createElement('p');
    title.className = 'sheet__bake-title';
    title.append(icon('i-clock'), document.createTextNode('今日の焼き上がり'));
    const times = document.createElement('p');
    times.className = 'sheet__bake-times';
    mine.forEach(({ slot, index }) => {
      const time = document.createElement('span');
      time.textContent = slot.time;
      if (index === plan.next) time.classList.add('is-next');
      if (plan.isPast(slot)) time.classList.add('is-past');
      times.append(time);
    });
    section.append(title, times);
    return section;
  }
  if (product.category === 'drink') {
    const title = document.createElement('p');
    title.className = 'sheet__bake-title';
    title.append(icon('i-cup'), document.createTextNode('イートイン'));
    const text = document.createElement('p');
    text.className = 'sheet__bake-text';
    text.textContent = `カフェのご利用は${shop.open}〜${shop.cafeClose}です。焼きたてのパンと一緒にどうぞ。`;
    section.append(title, text);
    return section;
  }
  return null;
}

function placeBake(body: HTMLElement, product: ProductView, plan: BakePlan, shop: ShopHours): void {
  const next = bakeSectionFor(product, plan, shop);
  const current = body.querySelector('.sheet__bake');
  if (!next) {
    current?.remove();
    return;
  }
  if (current) {
    current.replaceWith(next);
    return;
  }
  const anchor = body.querySelector('.btn-row, .note');
  if (anchor) anchor.before(next);
  else body.append(next);
}

export function renderProductSheet(body: HTMLElement, product: ProductView, plan: BakePlan, shop: ShopHours): void {
  body.replaceChildren();
  if (product.image) {
    const frame = document.createElement('span');
    frame.className = 'ph sheet__media';
    frame.dataset.label = product.image.label;
    const image = document.createElement('img');
    image.src = product.image.src;
    image.alt = product.image.alt;
    image.decoding = 'async';
    if (product.image.width) image.width = Number(product.image.width);
    if (product.image.height) image.height = Number(product.image.height);
    frame.append(image);
    body.append(frame);
  }
  const meta = document.createElement('div');
  meta.className = 'sheet__meta';
  meta.append(product.tag ?? defaultTag(product.line));
  const title = document.createElement('h2');
  title.className = 'sheet__title';
  title.id = 'sheet-title';
  title.textContent = product.name;
  body.append(meta, title);
  if (product.price) body.append(product.price);
  product.paragraphs.forEach((paragraph) => {
    const text = document.createElement('p');
    text.className = 'sheet__text';
    text.textContent = paragraph;
    body.append(text);
  });
  const row = document.createElement('div');
  row.className = 'btn-row';
  row.append(jumpLink('shop'), jumpLink('bake'));
  body.append(row);
  placeBake(body, product, plan, shop);
  watchImages(body);
}

function clonePrebuilt(id: string, scope: ParentNode): DocumentFragment | null {
  const detail = detailContent(id, scope);
  if (!detail?.querySelector('.sheet__title')) return null;
  const fragment = document.createDocumentFragment();
  Array.from(detail.childNodes).forEach((node) => fragment.append(node.cloneNode(true)));
  return fragment;
}

function productForBake(id: string, scope: ParentNode): ProductView {
  return readProductDetail(id, scope) ?? {
    id,
    name: '',
    paragraphs: [],
    image: null,
    price: null,
    tag: null,
    line: 'regular',
    category: '',
  };
}

export function jumpTo(hash: string): void {
  if (!hash.startsWith('#')) return;
  const target = document.querySelector(hash);
  if (!(target instanceof HTMLElement)) return;
  if (typeof target.scrollIntoView === 'function') {
    target.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth', block: 'start' });
  }
  if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
  try {
    target.focus({ preventScroll: true });
  } catch {
    target.focus();
  }
  const historyState = window.history;
  if (typeof historyState.replaceState === 'function') historyState.replaceState(null, '', hash);
}

export function openProductSheet(id: string, opener?: HTMLElement | null): boolean {
  const theme = window.bkeryTheme;
  if (!theme?.shop || !id) return false;
  const sheet = document.querySelector('#product-sheet');
  if (!(sheet instanceof HTMLDialogElement) || sheet.open) return false;
  const body = sheet.querySelector('[data-sheet-body]');
  if (!(body instanceof HTMLElement)) return false;

  const now = new Date();
  const shop = shopHoursOf(theme);
  const plan = getBakePlan(
    now.getHours() * 60 + now.getMinutes(),
    shop,
    bakeScheduleOf(theme),
    isClosedWeekday(now.getDay(), closedWeekdayList(theme.shop.closedWeekdays)),
  );
  const prebuilt = clonePrebuilt(id, document);
  if (prebuilt) {
    body.replaceChildren(prebuilt);
    placeBake(body, productForBake(id, document), plan, shop);
    watchImages(body);
  } else {
    const product = readProductDetail(id, document);
    if (!product) return false;
    renderProductSheet(body, product, plan, shop);
  }

  const panel = sheet.querySelector('[data-sheet-panel]');
  if (panel instanceof HTMLElement) panel.scrollTop = 0;
  openDialog(sheet, opener ?? null);
  return true;
}

const boundSheets = new WeakSet<HTMLDialogElement>();
let productClicksBound = false;

function onJump(sheet: HTMLDialogElement, event: Event): void {
  const link = closestElement(event.target, '[data-jump]');
  if (!(link instanceof HTMLAnchorElement)) return;
  event.preventDefault();
  closeDialog(sheet, () => jumpTo(link.getAttribute('href') ?? ''));
}

export function initProductSheet(): void {
  if (!window.bkeryTheme) return;
  const sheet = document.querySelector('#product-sheet');
  if (!(sheet instanceof HTMLDialogElement)) return;
  if (!sheet.querySelector('[data-sheet-body]')) return;
  if (!boundSheets.has(sheet)) {
    boundSheets.add(sheet);
    sheet.addEventListener('click', (event) => onJump(sheet, event));
  }
  if (productClicksBound) return;
  productClicksBound = true;
  document.addEventListener('click', (event) => {
    const trigger = closestElement(event.target, '[data-product]');
    if (!trigger?.dataset.product) return;
    openProductSheet(trigger.dataset.product, trigger);
  });
}
