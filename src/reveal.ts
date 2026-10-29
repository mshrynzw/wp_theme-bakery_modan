import { prefersReducedMotion } from './dom';

const ROOT_MARGIN = '0px 0px -8% 0px';

export function initReveal(scope: ParentNode = document): void {
  if (!window.bkeryTheme) return;
  const targets = Array.from(scope.querySelectorAll<HTMLElement>('[data-reveal]'));
  if (!targets.length) return;
  if (typeof IntersectionObserver === 'undefined' || prefersReducedMotion()) {
    targets.forEach((target) => target.classList.add('is-in'));
    return;
  }
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting || !(entry.target instanceof HTMLElement)) return;
      entry.target.classList.add('is-in');
      observer.unobserve(entry.target);
    });
  }, { rootMargin: ROOT_MARGIN });
  targets.forEach((target) => observer.observe(target));
}
