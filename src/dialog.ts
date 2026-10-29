import { focusWithoutScroll, prefersReducedMotion } from './dom';

export const DIALOG_CLOSE_MS = 380;
export const SHEET_DRAG_CLOSE_PX = 100;

let openCount = 0;
const bound = new WeakSet<HTMLDialogElement>();
const openers = new WeakMap<HTMLDialogElement, HTMLElement | null>();
const afters = new WeakMap<HTMLDialogElement, (() => void) | null>();
const locked = new WeakSet<HTMLDialogElement>();
const closing = new WeakSet<HTMLDialogElement>();

export function resetDialogState(): void {
  openCount = 0;
}

function lock(): void {
  openCount += 1;
  if (openCount === 1) document.documentElement.classList.add('is-locked');
}

function unlock(): void {
  openCount -= 1;
  if (openCount <= 0) {
    openCount = 0;
    document.documentElement.classList.remove('is-locked');
  }
}

function isOutside(target: EventTarget | null, dlg: HTMLDialogElement): boolean {
  if (target === dlg) return true;
  return target instanceof Element && target.classList.contains('lightbox__inner');
}

function cleanup(dlg: HTMLDialogElement): void {
  if (!closing.has(dlg) && !locked.has(dlg)) return;
  closing.delete(dlg);
  dlg.classList.remove('is-open', 'is-dragging');
  dlg.style.setProperty('--drag', '0px');
  if (locked.has(dlg)) {
    locked.delete(dlg);
    unlock();
  }
  dlg.dispatchEvent(new CustomEvent('modal:closed'));
  const after = afters.get(dlg) ?? null;
  afters.set(dlg, null);
  if (after) {
    after();
    return;
  }
  const opener = openers.get(dlg);
  if (opener?.isConnected) focusWithoutScroll(opener);
}

function focusClose(dlg: HTMLDialogElement): void {
  focusWithoutScroll(dlg.querySelector<HTMLElement>('[data-close]'));
}

function bindGrip(dlg: HTMLDialogElement, grip: HTMLElement): void {
  let startY: number | null = null;
  let dy = 0;

  grip.addEventListener('pointerdown', (event) => {
    startY = event.clientY;
    dy = 0;
    if (typeof grip.setPointerCapture === 'function') {
      try {
        grip.setPointerCapture(event.pointerId);
      } catch {
        // jsdom では pointer capture が例外になることがある。
      }
    }
    dlg.classList.add('is-dragging');
  });

  grip.addEventListener('pointermove', (event) => {
    if (startY === null) return;
    dy = Math.max(0, event.clientY - startY);
    dlg.style.setProperty('--drag', `${dy}px`);
  });

  const end = () => {
    if (startY === null) return;
    startY = null;
    dlg.classList.remove('is-dragging');
    if (dy > SHEET_DRAG_CLOSE_PX) closeDialog(dlg);
    else dlg.style.setProperty('--drag', '0px');
  };

  grip.addEventListener('pointerup', end);
  grip.addEventListener('pointercancel', end);
}

export function bindDialog(dlg: HTMLDialogElement): void {
  if (bound.has(dlg)) return;
  bound.add(dlg);

  dlg.addEventListener('cancel', (event) => {
    event.preventDefault();
    closeDialog(dlg);
  });
  dlg.addEventListener('close', () => cleanup(dlg));
  dlg.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || !dlg.open || closing.has(dlg)) return;
    event.preventDefault();
    closeDialog(dlg);
  });

  let downOutside = false;
  dlg.addEventListener('pointerdown', (event) => {
    downOutside = isOutside(event.target, dlg);
  });
  dlg.addEventListener('click', (event) => {
    if (downOutside && isOutside(event.target, dlg)) closeDialog(dlg);
    downOutside = false;
  });

  dlg.querySelectorAll<HTMLElement>('[data-close]').forEach((button) => {
    button.addEventListener('click', () => closeDialog(dlg));
  });

  const grip = dlg.querySelector('[data-sheet-grip]');
  if (grip instanceof HTMLElement) bindGrip(dlg, grip);
}

export function openDialog(dlg: HTMLDialogElement, opener?: HTMLElement | null): void {
  if (dlg.open) return;
  bindDialog(dlg);
  openers.set(dlg, opener ?? null);
  afters.set(dlg, null);
  dlg.style.setProperty('--drag', '0px');
  lock();
  locked.add(dlg);
  try {
    dlg.showModal();
  } catch {
    dlg.setAttribute('open', '');
  }
  if (!dlg.open) dlg.setAttribute('open', '');
  focusClose(dlg);
  window.requestAnimationFrame(() => {
    window.requestAnimationFrame(() => {
      if (!dlg.open) return;
      dlg.classList.add('is-open');
      focusClose(dlg);
    });
  });
  dlg.dispatchEvent(new CustomEvent('modal:open'));
}

export function closeDialog(dlg: HTMLDialogElement, after?: () => void): void {
  if (!dlg.open || closing.has(dlg)) return;
  closing.add(dlg);
  if (after) afters.set(dlg, after);
  dlg.classList.remove('is-open');
  window.setTimeout(() => {
    try {
      dlg.close();
    } catch {
      dlg.removeAttribute('open');
      dlg.dispatchEvent(new Event('close'));
    }
  }, prefersReducedMotion() ? 0 : DIALOG_CLOSE_MS);
}
