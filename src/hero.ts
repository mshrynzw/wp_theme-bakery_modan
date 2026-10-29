import { prefersReducedMotion, watchImages, watchReducedMotion } from './dom';

export const HERO_INTERVAL_MS = 6000;
const DOOR_FALLBACK_MS = 1200;
const SWIPE_PX = 40;

const resumes = new Set<() => void>();
let documentBound = false;

function bindDocument(): void {
  if (documentBound) return;
  documentBound = true;
  document.addEventListener('visibilitychange', () => {
    resumes.forEach((resume) => resume());
  });
}

/** 扉の開きは `html.is-loaded` を一度付けるだけ。スライド切替では外さない。 */
export function revealHeroDoor(doc: Document = document): void {
  const html = doc.documentElement;
  const first = doc.querySelector<HTMLImageElement>('.hero__slide img');
  let done = false;
  const go = () => {
    if (done) return;
    done = true;
    window.requestAnimationFrame(() => {
      html.classList.add('is-loaded');
    });
  };
  if (first && typeof first.decode === 'function') {
    void first.decode().then(go, go);
  } else {
    go();
  }
  window.setTimeout(go, DOOR_FALLBACK_MS);
}

export function initHero(): void {
  const rootNode = document.querySelector('[data-hero]');
  const dotsNode = document.querySelector('[data-hero-dots]');
  if (!(rootNode instanceof HTMLElement) || !(dotsNode instanceof HTMLElement)) return;
  const root: HTMLElement = rootNode;
  const dotsEl: HTMLElement = dotsNode;
  if (root.dataset.heroBound === '1') return;
  root.dataset.heroBound = '1';
  revealHeroDoor();

  const slides = Array.from(root.querySelectorAll<HTMLElement>('.hero__slide'));
  if (!slides.length) return;

  slides.forEach((slide, index) => {
    const image = slide.querySelector('img');
    if (!(image instanceof HTMLImageElement)) return;
    if (!image.hasAttribute('decoding')) {
      image.decoding = 'async';
      if (!image.hasAttribute('decoding')) image.setAttribute('decoding', 'async');
    }
    if (index > 0 && !image.hasAttribute('loading')) image.setAttribute('loading', 'lazy');
  });
  watchImages(root);

  let index = slides.findIndex((slide) => slide.classList.contains('is-active'));
  if (index < 0) {
    index = 0;
    slides[0].classList.add('is-active');
  }

  let inView = true;
  let hover = false;
  let timer = 0;

  dotsEl.style.setProperty('--hero-interval', `${HERO_INTERVAL_MS}ms`);
  dotsEl.replaceChildren();
  const dots = slides.map((_, dotIndex) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.setAttribute('aria-label', `写真${dotIndex + 1}を表示`);
    button.addEventListener('click', () => show(dotIndex));
    dotsEl.append(button);
    return button;
  });

  const canPlay = () => !prefersReducedMotion() && inView && !hover && !document.hidden;

  function mark(): void {
    dots.forEach((dot) => dot.removeAttribute('aria-current'));
    void dotsEl.offsetWidth;
    dots[index]?.setAttribute('aria-current', 'true');
  }

  function schedule(): void {
    window.clearTimeout(timer);
    const reduced = prefersReducedMotion();
    dotsEl.classList.toggle('is-static', reduced);
    dotsEl.classList.toggle('is-paused', !canPlay() && !reduced);
    if (!root.isConnected) return;
    if (canPlay()) timer = window.setTimeout(() => show(index + 1), HERO_INTERVAL_MS);
  }

  function show(next: number): void {
    if (!root.isConnected || slides.length === 0) return;
    slides[index]?.classList.remove('is-active');
    index = ((next % slides.length) + slides.length) % slides.length;
    slides[index].classList.add('is-active');
    mark();
    schedule();
  }

  function resume(): void {
    if (!root.isConnected) return;
    if (canPlay()) mark();
    schedule();
  }

  root.addEventListener('pointerenter', (event) => {
    if (event.pointerType !== 'mouse') return;
    hover = true;
    schedule();
  });
  root.addEventListener('pointerleave', (event) => {
    if (event.pointerType !== 'mouse') return;
    hover = false;
    resume();
  });

  let startX: number | null = null;
  let startY = 0;
  root.addEventListener('pointerdown', (event) => {
    startX = event.clientX;
    startY = event.clientY;
  });
  root.addEventListener('pointerup', (event) => {
    if (startX === null) return;
    const dx = event.clientX - startX;
    const dy = event.clientY - startY;
    startX = null;
    if (Math.abs(dx) > SWIPE_PX && Math.abs(dx) > Math.abs(dy)) show(index + (dx < 0 ? 1 : -1));
  });
  root.addEventListener('pointercancel', () => {
    startX = null;
  });

  if (typeof IntersectionObserver !== 'undefined') {
    const observer = new IntersectionObserver(([entry]) => {
      inView = Boolean(entry?.isIntersecting);
      resume();
    });
    observer.observe(root);
  }

  resumes.add(resume);
  bindDocument();
  watchReducedMotion(resume);
  mark();
  schedule();
}
