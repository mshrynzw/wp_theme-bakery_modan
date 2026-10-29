import { prefersReducedMotion } from './dom';

export type CarouselController = {
  index: () => number;
  go: (index: number) => void;
  sync: () => void;
};

const forces = new Set<() => void>();
let resizeBound = false;

function bindResize(): void {
  if (resizeBound) return;
  resizeBound = true;
  window.addEventListener('resize', () => {
    forces.forEach((force) => force());
  });
}

/** スクロール位置から現在のスライド番号を決める。幅が未確定のときは先頭のままにする。 */
export function carouselIndexFromScroll(scrollLeft: number, step: number, maxScroll: number, count: number): number {
  if (count <= 1) return 0;
  const safeStep = step > 0 ? step : 1;
  let index = Math.round(scrollLeft / safeStep);
  if (maxScroll > 0 && scrollLeft >= maxScroll - 4) index = count - 1;
  if (!Number.isFinite(index)) return 0;
  return Math.max(0, Math.min(count - 1, index));
}

export function createCarousel(root: HTMLElement): CarouselController | null {
  if (!window.bkeryTheme) return null;
  if (root.dataset.carouselBound === '1') return null;
  const trackNode = root.querySelector('.carousel__track');
  if (!(trackNode instanceof HTMLElement)) return null;
  const track: HTMLElement = trackNode;
  const slides = Array.from(track.querySelectorAll<HTMLElement>('.carousel__slide'));
  if (!slides.length) return null;
  root.dataset.carouselBound = '1';

  const prev = root.querySelector<HTMLButtonElement>('[data-prev]');
  const next = root.querySelector<HTMLButtonElement>('[data-next]');
  const dotsEl = root.querySelector<HTMLElement>('[data-dots]');
  const countEl = root.querySelector<HTMLElement>('[data-count]');
  let index = -1;

  if (!track.hasAttribute('tabindex')) track.tabIndex = 0;
  slides.forEach((slide, slideIndex) => {
    if (slide.hasAttribute('role')) return;
    slide.setAttribute('role', 'group');
    slide.setAttribute('aria-roledescription', 'スライド');
    slide.setAttribute('aria-label', `${slideIndex + 1} / ${slides.length}`);
  });

  if (dotsEl) {
    dotsEl.replaceChildren();
    slides.forEach((_, dotIndex) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.setAttribute('aria-label', `${dotIndex + 1}枚目を表示`);
      button.addEventListener('click', () => go(dotIndex));
      dotsEl.append(button);
    });
  }

  function update(): void {
    const step = slides.length > 1 ? slides[1].offsetLeft - slides[0].offsetLeft : 1;
    const maxScroll = track.scrollWidth - track.clientWidth;
    const nextIndex = carouselIndexFromScroll(track.scrollLeft, step, maxScroll, slides.length);
    if (nextIndex === index) return;
    index = nextIndex;
    if (dotsEl) {
      dotsEl.querySelectorAll('button').forEach((button, dotIndex) => {
        if (dotIndex === index) button.setAttribute('aria-current', 'true');
        else button.removeAttribute('aria-current');
      });
    }
    if (countEl) {
      countEl.replaceChildren();
      const current = document.createElement('b');
      current.textContent = String(index + 1);
      countEl.append(current, ` / ${slides.length}`);
    }
    if (prev) prev.disabled = index === 0;
    if (next) next.disabled = index === slides.length - 1;
  }

  function go(targetIndex: number): void {
    const nextIndex = Math.max(0, Math.min(slides.length - 1, targetIndex));
    const left = slides[nextIndex].offsetLeft - slides[0].offsetLeft;
    if (!prefersReducedMotion() && typeof track.scrollTo === 'function') {
      track.scrollTo({ left, behavior: 'smooth' });
      return;
    }
    track.scrollLeft = left;
    index = -1;
    update();
  }

  let frame = 0;
  track.addEventListener('scroll', () => {
    if (frame) return;
    frame = window.requestAnimationFrame(() => {
      frame = 0;
      update();
    });
  });

  prev?.addEventListener('click', () => go(index - 1));
  next?.addEventListener('click', () => go(index + 1));
  track.addEventListener('keydown', (event) => {
    if (event.target !== track) return;
    if (event.key === 'ArrowRight') {
      event.preventDefault();
      go(index + 1);
    } else if (event.key === 'ArrowLeft') {
      event.preventDefault();
      go(index - 1);
    }
  });

  const force = () => {
    if (!root.isConnected) {
      forces.delete(force);
      return;
    }
    index = -1;
    update();
  };
  forces.add(force);
  bindResize();
  update();

  return {
    index: () => index,
    go,
    sync: () => {
      index = -1;
      update();
    },
  };
}

export function initCarousel(): void {
  if (!window.bkeryTheme) return;
  document.querySelectorAll<HTMLElement>('[data-carousel]').forEach((root) => {
    createCarousel(root);
  });
}
