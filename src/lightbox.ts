import { prefersReducedMotion } from './dom';
import { openDialog } from './dialog';

export const LIGHTBOX_SWAP_MS = 160;
const SWIPE_PX = 50;

export function lightboxCaption(caption: string, index: number, total: number): string {
  return `${caption}（${index + 1} / ${total}）`;
}

const bound = new WeakSet<HTMLDialogElement>();

export function initLightbox(): void {
  if (!window.bkeryTheme) return;
  const box = document.querySelector('#lightbox');
  if (!(box instanceof HTMLDialogElement) || bound.has(box)) return;
  const triggers = Array.from(document.querySelectorAll<HTMLElement>('[data-lightbox]'));
  if (!triggers.length) return;
  const imageNode = box.querySelector('[data-lightbox-img]');
  const captionNode = box.querySelector('[data-lightbox-caption]');
  if (!(imageNode instanceof HTMLImageElement) || !(captionNode instanceof HTMLElement)) return;
  const img: HTMLImageElement = imageNode;
  const cap: HTMLElement = captionNode;
  bound.add(box);

  const items = triggers.map((trigger) => {
    const image = trigger.querySelector('img');
    return {
      src: image?.getAttribute('src') ?? '',
      alt: image?.alt ?? '',
      caption: trigger.querySelector('.gallery__caption')?.textContent?.trim() ?? '',
    };
  });
  let index = 0;

  function render(next: number, animate: boolean): void {
    index = ((next % items.length) + items.length) % items.length;
    const item = items[index];
    const apply = () => {
      img.src = item.src;
      img.alt = item.alt;
      cap.textContent = lightboxCaption(item.caption, index, items.length);
      img.classList.remove('is-swapping');
    };
    if (animate && !prefersReducedMotion()) {
      img.classList.add('is-swapping');
      window.setTimeout(apply, LIGHTBOX_SWAP_MS);
      return;
    }
    apply();
  }

  triggers.forEach((trigger, triggerIndex) => {
    trigger.addEventListener('click', () => {
      render(triggerIndex, false);
      openDialog(box, trigger);
    });
  });

  box.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => render(index - 1, true));
  box.querySelector('[data-lightbox-next]')?.addEventListener('click', () => render(index + 1, true));
  box.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') render(index - 1, true);
    if (event.key === 'ArrowRight') render(index + 1, true);
  });

  const inner = box.querySelector('.lightbox__inner');
  if (inner instanceof HTMLElement) {
    let startX: number | null = null;
    inner.addEventListener('pointerdown', (event) => {
      startX = event.clientX;
    });
    inner.addEventListener('pointerup', (event) => {
      if (startX === null) return;
      const dx = event.clientX - startX;
      startX = null;
      if (Math.abs(dx) > SWIPE_PX) render(index + (dx < 0 ? 1 : -1), true);
    });
  }
}
