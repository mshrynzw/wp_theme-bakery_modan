import { describe, expect, it } from 'vitest';
import { initReveal } from '../../../src/reveal';
import { installIntersectionObserver, installMatchMedia, sampleTheme } from './helpers';

function revealDom(): HTMLElement[] {
  document.body.innerHTML = `
    <p data-reveal>ひとつ</p>
    <p data-reveal>ふたつ</p>
  `;
  return Array.from(document.querySelectorAll<HTMLElement>('[data-reveal]'));
}

describe('initReveal', () => {
  it('shows the targets immediately without an observer', () => {
    window.bkeryTheme = sampleTheme();
    const targets = revealDom();
    initReveal();
    expect(targets.every((target) => target.classList.contains('is-in'))).toBe(true);
  });

  it('shows them immediately when motion is reduced', () => {
    window.bkeryTheme = sampleTheme();
    installMatchMedia((query) => query.includes('prefers-reduced-motion'));
    installIntersectionObserver();
    const targets = revealDom();
    initReveal();
    expect(targets.every((target) => target.classList.contains('is-in'))).toBe(true);
  });

  it('adds is-in when a target enters, then stops observing it', () => {
    window.bkeryTheme = sampleTheme();
    installMatchMedia(() => false);
    const io = installIntersectionObserver();
    const targets = revealDom();
    initReveal();
    expect(targets[0]?.classList.contains('is-in')).toBe(false);
    expect(io.observers[0]?.options?.rootMargin).toBe('0px 0px -8% 0px');
    io.intersect(0, targets[0]);
    expect(targets[0]?.classList.contains('is-in')).toBe(true);
    expect(targets[1]?.classList.contains('is-in')).toBe(false);
  });

  it('returns without a theme', () => {
    const targets = revealDom();
    initReveal();
    expect(targets[0]?.classList.contains('is-in')).toBe(false);
  });
});
