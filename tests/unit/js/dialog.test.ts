import { beforeEach, describe, expect, it, vi } from 'vitest';
import { closeDialog, openDialog } from '../../../src/dialog';
import { installMatchMedia, sampleTheme } from './helpers';

function sheet(id: string): HTMLDialogElement {
  const dlg = document.createElement('dialog');
  dlg.id = id;
  dlg.innerHTML = `
    <div data-sheet-grip><span></span></div>
    <button type="button" data-close>閉じる</button>
    <p class="inside">本文</p>
  `;
  document.body.append(dlg);
  return dlg;
}

describe('dialog', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    window.bkeryTheme = sampleTheme();
    installMatchMedia(() => false);
  });

  it('locks the page while any dialog is open and restores focus', async () => {
    const first = sheet('one');
    const second = sheet('two');
    const opener = document.createElement('button');
    opener.textContent = '開く';
    document.body.append(opener);
    opener.focus();

    openDialog(first, opener);
    expect(document.activeElement).toBe(first.querySelector('[data-close]'));
    openDialog(second, opener);
    expect(document.documentElement.classList.contains('is-locked')).toBe(true);

    await vi.advanceTimersByTimeAsync(50);
    expect(first.classList.contains('is-open')).toBe(true);

    first.querySelector<HTMLButtonElement>('[data-close]')?.click();
    expect(first.classList.contains('is-open')).toBe(false);
    expect(document.documentElement.classList.contains('is-locked')).toBe(true);
    await vi.advanceTimersByTimeAsync(380);
    expect(first.open).toBe(false);
    expect(document.documentElement.classList.contains('is-locked')).toBe(true);

    closeDialog(second);
    await vi.advanceTimersByTimeAsync(380);
    expect(document.documentElement.classList.contains('is-locked')).toBe(false);
    expect(document.activeElement).toBe(opener);
  });

  it('closes from Escape, the backdrop, and a downward drag past 100px', async () => {
    const dlg = sheet('sheet');
    openDialog(dlg, null);
    dlg.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
    await vi.advanceTimersByTimeAsync(380);
    expect(dlg.open).toBe(false);

    openDialog(dlg, null);
    dlg.dispatchEvent(new PointerEvent('pointerdown', { bubbles: true }));
    dlg.dispatchEvent(new MouseEvent('click', { bubbles: true }));
    await vi.advanceTimersByTimeAsync(380);
    expect(dlg.open).toBe(false);

    openDialog(dlg, null);
    const grip = dlg.querySelector<HTMLElement>('[data-sheet-grip]');
    grip?.dispatchEvent(new PointerEvent('pointerdown', { bubbles: true, clientY: 10, pointerId: 1 }));
    grip?.dispatchEvent(new PointerEvent('pointermove', { bubbles: true, clientY: 110, pointerId: 1 }));
    expect(dlg.style.getPropertyValue('--drag')).toBe('100px');
    grip?.dispatchEvent(new PointerEvent('pointerup', { bubbles: true, clientY: 110, pointerId: 1 }));
    expect(dlg.open).toBe(true);
    expect(dlg.style.getPropertyValue('--drag')).toBe('0px');

    grip?.dispatchEvent(new PointerEvent('pointerdown', { bubbles: true, clientY: 10, pointerId: 1 }));
    grip?.dispatchEvent(new PointerEvent('pointermove', { bubbles: true, clientY: 111, pointerId: 1 }));
    grip?.dispatchEvent(new PointerEvent('pointerup', { bubbles: true, clientY: 111, pointerId: 1 }));
    await vi.advanceTimersByTimeAsync(380);
    expect(dlg.open).toBe(false);
  });

  it('closes without the animation delay when motion is reduced', async () => {
    installMatchMedia((query) => query.includes('prefers-reduced-motion'));
    const dlg = sheet('sheet');
    openDialog(dlg, null);
    dlg.dispatchEvent(new Event('cancel', { cancelable: true }));
    await vi.advanceTimersByTimeAsync(0);
    expect(dlg.open).toBe(false);
  });

  it('does not close when the press starts inside the dialog', async () => {
    const dlg = sheet('sheet');
    openDialog(dlg, null);
    const inside = dlg.querySelector('.inside');
    inside?.dispatchEvent(new PointerEvent('pointerdown', { bubbles: true }));
    inside?.dispatchEvent(new MouseEvent('click', { bubbles: true }));
    await vi.advanceTimersByTimeAsync(380);
    expect(dlg.open).toBe(true);
  });
});
