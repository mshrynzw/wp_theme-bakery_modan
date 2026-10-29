/** 画面側の小さな DOM 操作。時刻計算は shop-status.ts に置いたままにする。 */

export function prefersReducedMotion(): boolean {
  return window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;
}

export function prefersReducedTransparency(): boolean {
  return window.matchMedia?.('(prefers-reduced-transparency: reduce)').matches ?? false;
}

export function watchReducedMotion(listener: () => void): void {
  watchMedia('(prefers-reduced-motion: reduce)', listener);
}

export function watchReducedTransparency(listener: () => void): void {
  watchMedia('(prefers-reduced-transparency: reduce)', listener);
}

function watchMedia(queryText: string, listener: () => void): void {
  const query = window.matchMedia?.(queryText);
  if (!query || typeof query.addEventListener !== 'function') return;
  query.addEventListener('change', listener);
}

export function focusWithoutScroll(element: HTMLElement | null | undefined): void {
  if (!element) return;
  try {
    element.focus({ preventScroll: true });
  } catch {
    element.focus();
  }
}

export function closestElement(target: EventTarget | null, selector: string): HTMLElement | null {
  const node = target instanceof Node ? target : null;
  const element = node instanceof Element ? node : node?.parentElement ?? null;
  const found = element?.closest(selector);
  return found instanceof HTMLElement ? found : null;
}

export function icon(id: string, className = ''): SVGSVGElement {
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('class', className ? `icon ${className}` : 'icon');
  svg.setAttribute('aria-hidden', 'true');
  const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
  use.setAttribute('href', `#${id}`);
  svg.append(use);
  return svg;
}

/** `.ph img` の読み込み完了と失敗を、モックと同じクラスで表す。 */
export function watchImages(root: ParentNode = document): void {
  root.querySelectorAll<HTMLImageElement>('.ph img').forEach((img) => {
    if (img.dataset.watched === '1') return;
    img.dataset.watched = '1';
    const ok = () => img.classList.add('is-loaded');
    const ng = () => img.closest('.ph')?.classList.add('is-missing');
    if (img.complete) {
      if (img.naturalWidth) ok();
      else ng();
    } else {
      img.addEventListener('load', ok, { once: true });
      img.addEventListener('error', ng, { once: true });
    }
  });
}
