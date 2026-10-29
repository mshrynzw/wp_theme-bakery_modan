import { prefersReducedMotion, watchImages } from './dom';
import { setStageScene } from './stage';

export const TOAST_MS = 2200;
const SECTION_ROOT_MARGIN = '-45% 0px -50% 0px';
const DOCK_THRESHOLDS = [0, 0.2, 0.4, 0.6, 0.8, 1];

let toastTimer = 0;

export function showToast(message: string, scope: ParentNode = document): void {
  const el = scope.querySelector('[data-toast]');
  if (!(el instanceof HTMLElement)) return;
  el.textContent = message;
  el.classList.add('is-visible');
  window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => {
    el.classList.remove('is-visible');
  }, TOAST_MS);
}

function applyLinks(theme: BkeryThemeData, scope: ParentNode): void {
  scope.querySelectorAll<HTMLAnchorElement>('[data-link]').forEach((anchor) => {
    const key = anchor.dataset.link;
    if (key !== 'instagram' && key !== 'map' && key !== 'calendar') return;
    const url = theme.links?.[key];
    if (typeof url === 'string' && url) anchor.href = url;
  });
  const handle = theme.links?.instagramHandle ?? '';
  scope.querySelectorAll<HTMLElement>('[data-insta-handle]').forEach((el) => {
    el.textContent = handle;
  });
}

function fallbackCopy(text: string): boolean {
  const area = document.createElement('textarea');
  area.value = text;
  area.setAttribute('readonly', '');
  area.style.position = 'fixed';
  area.style.opacity = '0';
  document.body.append(area);
  area.select();
  let ok = false;
  try {
    ok = typeof document.execCommand === 'function' ? document.execCommand('copy') : false;
  } catch {
    ok = false;
  }
  area.remove();
  return ok;
}

async function copyAddress(text: string): Promise<void> {
  let ok = false;
  try {
    if (navigator.clipboard?.writeText) {
      await navigator.clipboard.writeText(text);
      ok = true;
    } else {
      ok = fallbackCopy(text);
    }
  } catch {
    ok = fallbackCopy(text);
  }
  showToast(ok ? '住所をコピーしました' : 'コピーできませんでした。住所を長押しして選択してください');
}

function itemCategory(item: HTMLElement): string {
  return item.dataset.category ?? item.dataset.cat ?? '';
}

function buttonCategory(button: HTMLElement): string {
  return button.dataset.category ?? button.dataset.cat ?? '';
}

export function initMenuTabs(scope: ParentNode = document): void {
  if (!window.bkeryTheme) return;
  const tabs = scope.querySelector('[data-menu-tabs]');
  const grid = scope.querySelector('.menu__grid, [data-render="menu"]');
  if (!(tabs instanceof HTMLElement) || !(grid instanceof HTMLElement)) return;
  if (tabs.dataset.tabsBound === '1') return;
  tabs.dataset.tabsBound = '1';

  const items = () => Array.from(grid.querySelectorAll<HTMLElement>(':scope > li'));

  tabs.addEventListener('click', (event) => {
    const button = event.target instanceof Element ? event.target.closest('button') : null;
    if (!(button instanceof HTMLButtonElement) || !tabs.contains(button)) return;
    if (button.getAttribute('aria-pressed') === 'true') return;
    const category = buttonCategory(button);
    if (!category) return;
    tabs.querySelectorAll('button').forEach((candidate) => {
      candidate.setAttribute('aria-pressed', String(candidate === button));
    });
    let entering = 0;
    items().forEach((item) => {
      const show = category === 'all' || itemCategory(item) === category;
      item.hidden = !show;
      item.classList.remove('is-entering');
      if (show && !prefersReducedMotion()) {
        item.style.setProperty('--i', String(entering));
        entering += 1;
        void item.offsetWidth;
        item.classList.add('is-entering');
      }
    });
  });

  grid.addEventListener('animationend', (event) => {
    if (event.target instanceof HTMLElement) event.target.classList.remove('is-entering');
  });
}

function setActive(id: string, scope: ParentNode): void {
  scope.querySelectorAll<HTMLElement>('[data-nav]').forEach((link) => {
    if (link.dataset.nav === id) link.setAttribute('aria-current', 'true');
    else link.removeAttribute('aria-current');
  });
  setStageScene(id);
}

function initSectionWatch(scope: ParentNode): void {
  const sections = Array.from(scope.querySelectorAll<HTMLElement>('[data-section]'));
  setActive('hero', scope);
  if (typeof IntersectionObserver !== 'undefined') {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting || !(entry.target instanceof HTMLElement)) return;
        const id = entry.target.dataset.section;
        if (id) setActive(id, scope);
      });
    }, { rootMargin: SECTION_ROOT_MARGIN });
    sections.forEach((section) => observer.observe(section));
  }

  const dock = scope.querySelector<HTMLElement>('[data-dock]');
  const hero = scope.querySelector<HTMLElement>('#hero');
  if (!dock || !hero) return;
  dock.inert = true;
  if (typeof IntersectionObserver === 'undefined') return;
  const dockObserver = new IntersectionObserver(([entry]) => {
    if (!entry) return;
    const show = entry.intersectionRatio < 0.4;
    dock.classList.toggle('is-visible', show);
    dock.inert = !show;
  }, { threshold: DOCK_THRESHOLDS });
  dockObserver.observe(hero);
}

function initCopyAddress(scope: ParentNode): void {
  const button = scope.querySelector('[data-copy-address]');
  if (!(button instanceof HTMLButtonElement) || button.dataset.copyBound === '1') return;
  button.dataset.copyBound = '1';
  button.addEventListener('click', () => {
    void copyAddress(window.bkeryTheme?.shop.addressText ?? '');
  });
}

export function initShopUi(): void {
  const theme = window.bkeryTheme;
  if (!theme) return;
  applyLinks(theme, document);
  initCopyAddress(document);
  initMenuTabs(document);
  initSectionWatch(document);
  watchImages(document);
}
