import { beforeEach, describe, expect, it, vi } from 'vitest';
import { carouselIndexFromScroll, createCarousel, initCarousel } from '../../../src/carousel';
import { defineBox, installMatchMedia, sampleTheme } from './helpers';

function carouselDom(withDots = false): HTMLElement {
  document.body.innerHTML = `
    <div data-carousel="season">
      <ul class="carousel__track">
        <li class="carousel__slide"></li>
        <li class="carousel__slide"></li>
        <li class="carousel__slide"></li>
      </ul>
      <button type="button" data-prev></button>
      ${withDots ? '<div data-dots></div>' : '<p data-count></p>'}
      <button type="button" data-next></button>
    </div>
  `;
  const root = document.querySelector<HTMLElement>('[data-carousel]');
  if (!root) throw new Error('missing carousel');
  const track = root.querySelector<HTMLElement>('.carousel__track');
  const slides = root.querySelectorAll<HTMLElement>('.carousel__slide');
  if (!track) throw new Error('missing track');
  defineBox(slides[0], 'offsetLeft', 0);
  defineBox(slides[1], 'offsetLeft', 320);
  defineBox(slides[2], 'offsetLeft', 640);
  defineBox(track, 'scrollWidth', 960);
  defineBox(track, 'clientWidth', 320);
  return root;
}

describe('carouselIndexFromScroll', () => {
  it('stays on the first slide until the track has a width', () => {
    expect(carouselIndexFromScroll(0, 0, 0, 3)).toBe(0);
  });

  it('snaps to the last slide at the scroll end', () => {
    expect(carouselIndexFromScroll(280, 320, 640, 3)).toBe(1);
    expect(carouselIndexFromScroll(636, 320, 640, 3)).toBe(2);
    expect(carouselIndexFromScroll(-10, 320, 640, 3)).toBe(0);
  });
});

describe('createCarousel', () => {
  beforeEach(() => {
    window.bkeryTheme = sampleTheme();
    installMatchMedia((query) => query.includes('prefers-reduced-motion'));
  });

  it('returns null without a theme', () => {
    window.bkeryTheme = undefined;
    expect(createCarousel(carouselDom())).toBeNull();
  });

  it('moves with buttons, clamps the ends, and updates the count', () => {
    const root = carouselDom();
    const carousel = createCarousel(root);
    expect(carousel).not.toBeNull();
    const prev = root.querySelector<HTMLButtonElement>('[data-prev]');
    const next = root.querySelector<HTMLButtonElement>('[data-next]');
    expect(prev?.disabled).toBe(true);
    expect(next?.disabled).toBe(false);
    expect(root.querySelector('[data-count]')?.textContent).toBe('1 / 3');
    expect(root.querySelector('.carousel__slide')?.getAttribute('aria-label')).toBe('1 / 3');

    next?.click();
    expect(carousel?.index()).toBe(1);
    expect(prev?.disabled).toBe(false);
    expect(root.querySelector('[data-count] b')?.textContent).toBe('2');

    carousel?.go(9);
    expect(carousel?.index()).toBe(2);
    expect(next?.disabled).toBe(true);
  });

  it('moves with arrow keys only when the track itself is focused', () => {
    const root = carouselDom();
    const carousel = createCarousel(root);
    const track = root.querySelector<HTMLElement>('.carousel__track');
    track?.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true }));
    expect(carousel?.index()).toBe(1);
    const slide = root.querySelector('.carousel__slide');
    slide?.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true }));
    expect(carousel?.index()).toBe(1);
    track?.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowLeft', bubbles: true }));
    expect(carousel?.index()).toBe(0);
  });

  it('selects a dot and calls smooth scrolling when motion is allowed', () => {
    installMatchMedia(() => false);
    const root = carouselDom(true);
    const track = root.querySelector<HTMLElement>('.carousel__track');
    const scrollTo = vi.fn();
    if (track) track.scrollTo = scrollTo;
    createCarousel(root);
    const dots = root.querySelectorAll('[data-dots] button');
    expect(dots[1]?.getAttribute('aria-label')).toBe('2枚目を表示');
    dots[1]?.dispatchEvent(new MouseEvent('click', { bubbles: true }));
    expect(scrollTo).toHaveBeenCalledWith({ left: 320, behavior: 'smooth' });
  });

  it('initializes every carousel on the page', () => {
    carouselDom();
    initCarousel();
    expect(document.querySelector('[data-prev]')?.hasAttribute('disabled')).toBe(true);
  });
});
